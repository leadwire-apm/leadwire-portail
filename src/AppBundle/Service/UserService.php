<?php declare(strict_types=1);

namespace AppBundle\Service;

use ATS\EmailBundle\Document\Email;
use ATS\EmailBundle\Service\SimpleMailerService;
use ATS\PaymentBundle\Service\CustomerService;
use ATS\PaymentBundle\Service\Subscription;
use ATS\PaymentBundle\Service\PlanService;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use AppBundle\Manager\UserManager;
use AppBundle\Document\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

/**
 * Service class for User entities
 *
 */
class UserService
{
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SimpleMailerService
     */
    private $mailer;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var string
     */
    private $sender;

    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @var Subscription
     */
    private $subscriptionService;

    /**
     * @var PlanService
     */
    private $planService;

    /**
     * Constructor
     *
     * @param UserManager $userManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param SimpleMailerService $mailer
     * @param Router $router
     * @param ContainerInterface $container
     * @param CustomerService $customerService
     * @param Subscription $subscriptionService
     * @param PlanService $planService
     */
    public function __construct(
        UserManager $userManager,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        SimpleMailerService $mailer,
        Router $router,
        ContainerInterface $container,
        CustomerService $customerService,
        Subscription $subscriptionService,
        PlanService $planService
    ) {
        $this->userManager = $userManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->sender = $container->getParameter('sender');
        $this->customerService = $customerService;
        $this->subscriptionService = $subscriptionService;
        $this->planService = $planService;
    }

    /**
     * List all users
     *
     * @return array
     */
    public function listUsers()
    {
        return $this->userManager->getAll();
    }

    /**
     * Paginates through Users
     *
     * @param int $pageNumber
     * @param int $itemsPerPage
     * @param array $criteria
     *
     * @return array
     */
    public function paginate($pageNumber = 1, $itemsPerPage = 20, array $criteria = [])
    {
        return $this->userManager->paginate($criteria, $pageNumber, $itemsPerPage);
    }

    public function subscribe($data, User $user)
    {
        $json = json_encode(["name" => $user->getName(), "email" => $user->getEmail()]);
        $data = json_decode($data, true);
        $plan = $this->planService->getPlan($data['plan']);
        $token = null;
        if ($plan) {
            if ($plan->getPrice() == 0) {
                if ($user->getSubscriptionId()) {
                    $this->subscriptionService->delete(
                        $user->getSubscriptionId(),
                        $user->getCustomer()->getGatewayToken()
                    );
                }
                $user->setPlan($plan);
                $this->userManager->update($user);
                return true;
            } else {
                foreach ($plan->getPrices() as $pricingPlan) {
                    if ($pricingPlan->getName() == $data['billingType']) {
                        $token = $pricingPlan->getToken();
                    }
                }

                if (!$user->getCustomer()) {
                    $customer = $this->customerService->newCustomer($json, $data['card']);
                    $user->setCustomer($customer);
                } else {
                    $customer = $user->getCustomer();
                }

                if ($subscriptionId = $this->subscriptionService->create(
                    $token,
                    $customer
                )
                ) {
                    $user->setSubscriptionId($subscriptionId);
                    $user->setPlan($plan);
                    $this->userManager->update($user);
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            throw new \Exception("Plan Not found.");
        }
    }

    public function getSubscription(User $user)
    {
        if ($user->getSubscriptionId() && $user->getCustomer()) {
            return $this->subscriptionService->get($user->getSubscriptionId(), $user->getCustomer());
        } else {
            return [];
        }
    }

    public function getInvoices(User $user)
    {
        if ($user->getCustomer()) {
            return $this->customerService->getInvoices($user->getCustomer()->getGatewayToken());
        } else {
            return [];
        }
    }

    public function updateSubscription(User $user, $data)
    {
        $plan = $this->planService->getPlan($data['plan']);
        $token = false;

        if ($plan) {
            if ($plan->getPrice() == 0) {
                $this->subscriptionService->delete(
                    $user->getSubscriptionId(),
                    $user->getCustomer()->getGatewayToken()
                );
                $user->setPlan($plan);
                $this->userManager->update($user);
                return true;
            } else {
                foreach ($plan->getPrices() as $billingType) {
                    if ($billingType->getName() == $data['billingType']) {
                        $token = $billingType->getToken();
                    }
                }

                if (is_string($token)) {
                    $anchorCycle = isset($data['periodEnd']) ? $data['periodEnd'] : 'uncharged';
                    $data = $this->subscriptionService->update(
                        $user->getCustomer()->getGatewayToken(),
                        $user->getSubscriptionId(),
                        $token,
                        $anchorCycle
                    );
                    $user->setPlan($plan);
                    $this->userManager->update($user);
                    return $data;
                } else {
                    throw new \Exception("Plan was not found");
                }
            }
        } else {
            throw new \Exception("Plan was not found");
        }
    }

    public function updateCreditCard(User $user, $data)
    {
        return $this->customerService->updateCard($user->getCustomer(), $data);
    }

    /**
     * Get a specific user
     *
     * @param string $id
     *
     * @return User
     */
    public function getUser($id)
    {
        return $this->userManager->getOneBy(['id' => $id]);
    }

    /**
     * Get specific users
     *
     * @param string $criteria
     *
     * @return array
     */
    public function getUsers(array $criteria = [])
    {
        return $this->userManager->getBy($criteria);
    }

    /**
     * Creates a new user from JSON data
     *
     * @param string $json
     *
     * @return bool
     */
    public function newUser($json)
    {
        $user = $this
            ->serializer
            ->deserialize($json, User::class, 'json');

        return $this->updateUser($json);
    }

    /**
     * Updates a specific user from JSON data
     *
     * @param string $json
     *
     * @return bool
     */
    public function updateUser($json, $id)
    {
        $isSuccessful = false;

        try {
            $user = $this
                ->serializer
                ->deserialize($json, User::class, 'json');

            $this->userManager->update($user);
            if (!$user->getIsEmailValid()) {
                $this->sendVerifEmail($user);
            }
            $isSuccessful = true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $isSuccessful = false;
        }

        return $isSuccessful;
    }

    /**
     * Deletes a specific user from JSON data
     *
     * @param string $id
     *
     * @return void
     */
    public function deleteUser($id)
    {
        $this->userManager->deleteById($id);
    }

    /**
     * Performs a full text search on  User
     *
     * @param string $term
     * @param string $lang
     *
     * @return array
     */
    public function textSearch($term, $lang)
    {
        return $this->userManager->textSearch($term, $lang);
    }

    /**
     * Performs multi-field grouped query on User
     * @param array $searchCriteria
     * @param string $groupField
     * @param \Closure $groupValueProcessor
     * @return array
     */
    public function getAndGroupBy(array $searchCriteria, $groupFields = [], $valueProcessors = [])
    {
        return $this->userManager->getAndGroupBy($searchCriteria, $groupFields, $valueProcessors);
    }

    /**
     * @param User $user
     */
    public function sendVerifEmail(User $user)
    {
        $mail = new Email();
        $mail
            ->setSubject("LeadWire: Email verification")
            ->setSenderName("LeadWire")
            ->setSenderAddress($this->sender)
            ->setTemplate('AppBundle:Mail:verif.html.twig')
            ->setRecipientAddress($user->getEmail())
            ->setMessageParameters([
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'link' => $this->router->generate('verify_email', ['email' => $user->getEmail()], UrlGeneratorInterface::ABSOLUTE_URL)
            ]);
        $this->mailer->send($mail, false);
    }
}
