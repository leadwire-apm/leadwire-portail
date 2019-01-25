<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\Invitation;
use AppBundle\Document\User;
use AppBundle\Manager\InvitationManager;
use ATS\EmailBundle\Document\Email;
use ATS\EmailBundle\Service\SimpleMailerService;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

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
     * Constructor
     *
     * @param InvitationManager $invitationManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param SimpleMailerService $mailer
     * @param Router $router
     * @param LdapService $ldap
     * @param ApplicationService $applicationService
     */
    public function __construct(
        InvitationManager $invitationManager,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        SimpleMailerService $mailer,
        RouterInterface $router,
        LdapService $ldap,
        ApplicationService $applicationService,
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
    }

    /**
     * List all invitations
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

            /** @var Application $application */
            $application = $this->applicationService->getApplication((string) $invitation->getApplication()->getId());

            if ($application instanceof Application) {
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
        $mail
            ->setSubject("LeadWire: Invitation to access to an application")
            ->setSenderName("LeadWire")
            ->setSenderAddress($this->sender)
            ->setTemplate('AppBundle:Mail:AppInvitation.html.twig')
            ->setRecipientAddress($invitation->getEmail())
            ->setMessageParameters(
                [
                    'inviter' => $user->getUsername(),
                    'email' => $invitation->getEmail(),
                    'invitation' => $invitation->getId(),
                    'link' => $this->router->generate('angular_endPoint', [], UrlGeneratorInterface::ABSOLUTE_URL)
                ]
            );

        $this->mailer->send($mail, true);
    }
}
