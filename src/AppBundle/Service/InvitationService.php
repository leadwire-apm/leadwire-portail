<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\User;
use Psr\Log\LoggerInterface;
use AppBundle\Document\Invitation;
use AppBundle\Manager\UserManager;
use AppBundle\Document\Application;
use ATS\EmailBundle\Document\Email;
use Symfony\Component\Routing\Router;
use JMS\Serializer\SerializerInterface;
use AppBundle\Manager\InvitationManager;
use AppBundle\Document\ApplicationPermission;
use Symfony\Component\Routing\RouterInterface;
use ATS\EmailBundle\Service\SimpleMailerService;
use AppBundle\Manager\ApplicationPermissionManager;
use AppBundle\Service\ApplicationPermissionService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Service\ElasticSearchService;
use AppBundle\Service\EnvironmentService;
use AppBundle\Document\AccessLevel;

/**
 * Service class for Invitation entities
 *
 */
class InvitationService
{
    /**
     * @var InvitationManager
     */
    private $invitationManager;

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
     * @var RouterInterface
     */
    private $router;

    /**
     * @var string
     */
    private $sender;

    /**
     * @var LdapService
     */
    private $ldap;

    /**
     * @var ApplicationService
     */
    private $applicationService;

    /**
     * @var ApplicationPermissionService
     */
    private $permissionService;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var ElasticSearchService
     */
    private $elasticSearchService;

    /**
     * @var EnvironmentService
     */
    private $environmentService;

    /**
     * Constructor
     *
     * @param InvitationManager $invitationManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param SimpleMailerService $mailer
     * @param Router $router
     * @param LdapService $ldap
     * @param ApplicationService $applicationService
     * @param ApplicationPermissionService $permissionService
     * @param UserManager $userManager
     * @param ElasticSearchService $elasticSearchService
     * @param EnvironmentService $environmentService
     */
    public function __construct(
        InvitationManager $invitationManager,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        SimpleMailerService $mailer,
        RouterInterface $router,
        LdapService $ldap,
        ApplicationService $applicationService,
        ApplicationPermissionService $permissionService,
        UserManager $userManager,
        ElasticSearchService $elasticSearchService,
        EnvironmentService $environmentService,
        string $sender
    ) {
        $this->invitationManager = $invitationManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->mailer =
        $this->mailer = $mailer;
        $this->router = $router;
        $this->sender = $sender;
        $this->ldap = $ldap;
        $this->applicationService = $applicationService;
        $this->permissionService = $permissionService;
        $this->userManager = $userManager;
        $this->es = $elasticSearchService;
        $this->environmentService = $environmentService;
    }

    /**
     * List all invitations
     *
     * @codeCoverageIgnore
     *
     * @return array
     */
    public function listInvitations()
    {
        return $this->invitationManager->getAll();
    }

    /**
     * Paginates through Invitations
     *
     * @codeCoverageIgnore
     * @param int $pageNumber
     * @param int $itemsPerPage
     * @param array $criteria
     *
     * @return array
     */
    public function paginate($pageNumber = 1, $itemsPerPage = 20, array $criteria = [])
    {
        return $this->invitationManager->paginate($criteria, $pageNumber, $itemsPerPage);
    }

    /**
     * Get a specific invitation
     *
     * @codeCoverageIgnore
     * @param string $id
     *
     * @return Invitation
     */
    public function getInvitation($id)
    {
        return $this->invitationManager->getOneBy(['id' => $id]);
    }

    /**
     * Get specific invitations
     *
     * @codeCoverageIgnore
     * @param array $criteria
     *
     * @return array
     */
    public function getInvitations(array $criteria = [])
    {
        return $this->invitationManager->getBy($criteria);
    }

    /**
     * Creates a new invitation from JSON data
     *
     * @codeCoverageIgnore
     *
     * @param string $json
     *
     * @param User $user
     * @return \MongoId
     */
    public function newInvitation($json, User $user)
    {
        $invitation = $this
            ->serializer
            ->deserialize($json, Invitation::class, 'json');

        $id = $this->invitationManager->update($invitation);
        $this->sendInvitationMail($this->getInvitation((string)$id), $user);

        return $id;
    }

    /**
     * Updates a specific invitation from JSON data
     *
     * @param string $json
     *
     * @return bool
     */
    public function updateInvitation($json)
    {
        $isSuccessful = false;

        try {
            /** @var Invitation $invitation */
            $invitation = $this->serializer->deserialize($json, Invitation::class, 'json');

            /** @var Application|null $application */
            $application = $this->applicationService->getApplication((string) $invitation->getApplication()->getId());

            if ($application !== null) {
                $invitation->setApplication($application);
            } else {
                throw new \Exception(sprintf("Unknown application %s", $invitation->getApplication()->getId()));
            }

            if ($invitation->getApplication()->getOwner()->getId() !== $invitation->getUser()->getId()) {
                $this->invitationManager->update($invitation);
                $this->ldap->createInvitationEntry($invitation);
                $isSuccessful = true;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $isSuccessful = false;
        }

        return $isSuccessful;
    }

    /**
     * Deletes a specific invitation from JSON data
     *
     * @codeCoverageIgnore
     *
     * @param string $id
     *
     * @return void
     */
    public function deleteInvitation($id)
    {
        $this->invitationManager->deleteById($id);
    }

    public function sendInvitationMail(Invitation $invitation, User $user)
    {
        $mail = new Email();
        $application = $this->applicationService->getApplication((string)$invitation->getApplication()->getId());
        $mail
            ->setSubject("LeadWire: Invitation to access to an application")
            ->setSenderName("LeadWire")
            ->setSenderAddress($this->sender)
            ->setTemplate('AppBundle:Mail:AppInvitation.html.twig')
            ->setRecipientAddress($invitation->getEmail())
            ->setSentAt(new \DateTime())
            ->setMessageParameters(
                [
                    'inviter' => $user->getName(),
                    'invitation_id' => $invitation->getId(),
                    'application' => $application !== null ? $application->getName() : 'an application',
                    'email' => $invitation->getEmail(),
                    'link' => $this->router->generate('angular_endPoint', [], UrlGeneratorInterface::ABSOLUTE_URL)
                ]
            );

        $this->mailer->send($mail, true);
    }

    /**
     * @param string $id
     * @param string $userId
     *
     * @return void
     */
    public function acceptInvitation($id, $userId)
    {
        $invitation = $this->invitationManager->getOneBy(['id' => $id, 'isPending' => true]);
        $invitedUser = $this->userManager->getOneBy(['id' => $userId]);

        if ($invitation instanceof Invitation && $invitedUser instanceof User)  {
            $invitation->setPending(false);
            $invitation->setUser($invitedUser);
            $application = $invitation->getApplication();
            $this->permissionService->grantPermission($application, $invitedUser, ApplicationPermission::ACCESS_GUEST);
            $this->invitationManager->update($invitation);
            $this->userManager->update($invitedUser);

            foreach ($application->getEnvironments() as $environment) {
                $envName = $environment->getName();
                $this->es->updateRoleMapping("add", $envName, $invitedUser, $application->getName(), false, false);
               
                // set app data access level to read for application
                $invitedUser->addAccessLevel((new AccessLevel())
                        ->setEnvironment($environment)
                        ->setApplication($application)
                        ->setLevel(AccessLevel::ACCESS)
                        ->setAccess(AccessLevel::VIEWER ));

                $this->userManager->update($invitedUser);
            }
        } 
    }

    /**
     * @param string $id
     * @param string $userId
     *
     * @return user
     */
    public function grantPermission($appId, $userId)
    {
        $invitedUser = $this->userManager->getOneBy(['id' => $userId]);
        $application = $this->applicationService->getApplication($appId);
        $invitation = new Invitation();
        $inv = $this->invitationManager->getOneBy(['user' => $invitedUser, 'application' => $application]);
       
        if($inv === null){
            $invitation->setApplication($application);
            $invitation->setUser($invitedUser);
            $invitation->setEmail($invitedUser->getEmail());
            $invitation->setPending(false);
        }else{
            $inv->setPending(false);
            $invitation = $inv;
        }


        $this->invitationManager->update($invitation);

        $this->permissionService->grantPermission($application, $invitedUser, ApplicationPermission::ACCESS_GUEST);

        $this->userManager->update($invitedUser);

        foreach ($application->getEnvironments() as $environment) {
            $envName = $environment->getName();
            $this->es->updateRoleMapping("add", $envName, $invitedUser, $application->getName(), false, false);
            
            // set app data access level to read for invited user
            $invitedUser->addAccessLevel((new AccessLevel())
                    ->setEnvironment($environment)
                    ->setApplication($application)
                    ->setLevel(AccessLevel::ACCESS)
                    ->setAccess(AccessLevel::VIEWER));

            $this->userManager->update($invitedUser);
        }
        return $invitedUser;
    }

    /**
     * @param string $id
     * @param string $userId
     *
     * @return user
     */
    public function RevokePermission($appId, $userId)
    {
        $invitedUser = $this->userManager->getOneBy(['id' => $userId]);
        $application = $this->applicationService->getApplication($appId);

        $invitation = $this->invitationManager->getOneBy(['user' => $invitedUser, 'application' => $application]);
       
        if($invitation){
            $this->invitationManager->deleteById($invitation->getId());
        }

        $this->permissionService->removeApplicationPermissionsByUser($application, $invitedUser);

        foreach ($application->getEnvironments() as $environment) {
            $envName = $environment->getName();
            $envId = $environment->getId();
           
            $accessLevel = $invitedUser->getAccessLevelsApp($envId, $appId, AccessLevel::ACCESS);
            if($accessLevel){
                $invitedUser->removeAccessLevel($accessLevel);
                $this->es->updateRoleMapping("delete", $envName, $invitedUser, $application->getName(), true, false);
                $this->es->updateRoleMapping("delete", $envName, $invitedUser, $application->getName(), false, false);
            }
        }
        $this->userManager->update($invitedUser);
    }
}
