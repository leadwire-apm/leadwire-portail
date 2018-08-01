<?php declare(strict_types=1);

namespace AppBundle\Service;

use ATS\EmailBundle\Document\Email;
use ATS\EmailBundle\Service\SimpleMailerService;
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
     * Constructor
     *
     * @param UserManager $userManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param SimpleMailerService $mailer
     * @param Router $router
     */
    public function __construct(UserManager $userManager, SerializerInterface $serializer, LoggerInterface $logger, SimpleMailerService $mailer, Router $router, ContainerInterface $container)
    {
        $this->userManager = $userManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->router = $router;
        $this->sender = $container->getParameter('sender');
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
            $tmpUser = json_decode($json, true);
            $user = $this->getUser($id);
            foreach ($tmpUser as $field => $value) {
                $fn = 'set' . ucfirst($field);
                if (method_exists($user, $fn)) {
                    $user->{$fn}($value);
                }
            }

            $this->userManager->update($user);
            if ($tmpUser['email']) {
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
        $this->mailer->send($mail, true);
    }
}
