<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\User;
use AppBundle\Manager\UserManager;
use ATS\EmailBundle\Document\Email;
use ATS\EmailBundle\Service\SimpleMailerService;
use ATS\PaymentBundle\Service\CustomerService;
use ATS\PaymentBundle\Service\PlanService;
use ATS\PaymentBundle\Service\Subscription;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
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

    public function subscribe($data, User $user)
    {
        /** @var string $json */
        $json = json_encode(["name" => $user->getName(), "email" => $user->getEmail()]);
        $data = json_decode($data, true);
        $plan = $this->planService->getPlan($data['plan']);
        $token = null;

        if ($plan !== null) {
            if ($plan->getPrice() === 0.0) {
                if ($user->getSubscriptionId() !== null) {
                    $this->subscriptionService->delete(
                        $user->getSubscriptionId(),
                        $user->getCustomer() !== null ? $user->getCustomer()->getGatewayToken() : ''
                    );
                }
                $user->setPlan($plan);
                $this->userManager->update($user);

                return true;
            } else {
                foreach ($plan->getPrices() as $pricingPlan) {
                    if ($pricingPlan->getName() === $data['billingType']) {
                        $token = $pricingPlan->getToken();
                    }
                }

                if ($user->getCustomer() === null) {
                    $customer = $this->customerService->newCustomer($json, $data['card']);
                    $user->setCustomer($customer);
                } else {
                    $customer = $user->getCustomer();
                }

                $subscriptionId = $this->subscriptionService->create($token, $customer);

                if ($subscriptionId !== null) {
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
        if ($user->getSubscriptionId() !== null && $user->getCustomer() !== null) {
            return $this->subscriptionService->get($user->getSubscriptionId(), $user->getCustomer());
        } else {
            return [];
        }
    }

    public function getInvoices(User $user)
    {
        if ($user->getCustomer() !== null) {
            return $this->customerService->getInvoices($user->getCustomer()->getGatewayToken());
        } else {
            return [];
        }
    }

    /**
     *
     * @param User $user
     * @param array $data
     *
     * @return mixed
     */
    public function updateSubscription(User $user, $data)
    {
        if ($user->getCustomer() === null) {
            throw new \Exception(sprintf("Customer for user %s is null", $user->getId()));
        }

        $plan = $this->planService->getPlan($data['plan']);
        $subscription = $this->subscriptionService->get($user->getSubscriptionId(), $user->getCustomer());
        $token = false;

        if ($plan !== null) {
            $anchorCycle = 'unchanged';
            if ($plan->getPrice() === 0.0) {
                $this->subscriptionService->delete(
                    $user->getSubscriptionId(),
                    $user->getCustomer()->getGatewayToken()
                );
                $user->setPlan($plan);
                $this->userManager->update($user);

                return true;
            } else {
                foreach ($plan->getPrices() as $billingType) {
                    if ($billingType->getName() === $data['billingType']) {
                        $token = $billingType->getToken();
                    }
                }

                if (is_string($token) === true) {
                    if ($user->getPlan()->getPrice() < $plan->getPrice() &&
                        $subscription["plan"]["interval"] . 'ly' !== $data['billingType']
                    ) {
                        $anchorCycle = 'now';
                    }

                    if ($user->getPlan()->getPrice() === 0.0) {
                        $data = $this->subscriptionService->create(
                            $token,
                            $user->getCustomer()
                        );
                    } else {
                        $data = $this->subscriptionService->update(
                            $user->getCustomer()->getGatewayToken(),
                            $user->getSubscriptionId(),
                            $token,
                            $anchorCycle
                        );
                    }
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
        if ($user->getCustomer() !== null) {
            $customer = $user->getCustomer();
        } else {
            /** @var string $json */
            $json = json_encode(["name" => $user->getName(), "email" => $user->getEmail()]);
            $data = json_decode($data, true);

            $customer = $this->customerService->newCustomer($json, $data);
            $user->setCustomer($customer);
            $this->userManager->update($user);
        }
        return $this->customerService->updateCard($customer, $data);
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
     * @param array $criteria
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
        return $this->updateUser($json, null);
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

        $context = new DeserializationContext();
        $context->setSerializeNull(true);

        try {
            $user = $this
                ->serializer
                ->deserialize($json, User::class, 'json', $context);

            $this->userManager->update($user);
            if ($user instanceof User && $user->getIsEmailValid() === false) {
                $this->sendVerificationEmail($user);
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
     * @param User $user
     */
    public function sendVerificationEmail(User $user)
    {
        $mail = new Email();
        $mail
            ->setSubject("LeadWire: Email verification")
            ->setSenderName("LeadWire")
            ->setSenderAddress($this->sender)
            ->setTemplate('AppBundle:Mail:verif.html.twig')
            ->setRecipientAddress($user->getEmail())
            ->setMessageParameters(
                [
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),
                    'link' => $this->router->generate('verify_email', ['email' => $user->getEmail()], UrlGeneratorInterface::ABSOLUTE_URL),
                ]
            );
        $this->mailer->send($mail, false);
    }

    /**
     *
     * @param string $id
     *
     * @return boolean
     */
    public function softDeleteUser(string $id): bool
    {
        $isSuccessful = false;
        /** @var User $user */
        $user = $this->userManager->getOneBy(['id' => $id]);

        if ($user instanceof User) {
            $user->setDeleted(true);
            $this->userManager->update($user);

            $isSuccessful = true;
        }

        return $isSuccessful;
    }
}
