<?php declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Document\User;
use ATS\EmailBundle\Document\Email;
use ATS\EmailBundle\Service\SimpleMailerService;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use AppBundle\Manager\InvitationManager;
use AppBundle\Document\Invitation;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

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
     * @var Router
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
     * @var AppService
     */
    private $appService;

    /**
     * Constructor
     *
     * @param InvitationManager $invitationManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param SimpleMailerService $mailer
     * @param Router $router
     * @param ContainerInterface $container
     * @param LdapService $ldap
     * @param AppService $appService
     */
    public function __construct(InvitationManager $invitationManager, SerializerInterface $serializer, LoggerInterface $logger, SimpleMailerService $mailer, Router $router, ContainerInterface $container, LdapService $ldap, AppService $appService)
    {
        $this->invitationManager = $invitationManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->mailer =
        $this->mailer = $mailer;
        $this->router = $router;
        $this->sender = $container->getParameter('sender');
        $this->ldap = $ldap;
        $this->appService = $appService;
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
     * @param string $criteria
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
//        $this->sendInvitationMail($this->getInvitation($id), $user);
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
            $invitation = $this->serializer->deserialize($json, Invitation::class, 'json');
            $invitation->setApp($this->appService->getApp($invitation->getApp()->getId()));
            $this->invitationManager->update($invitation);
            $this->ldap->createLdapInvitationEntry($invitation);
            $isSuccessful = true;
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

     /**
      * Performs a full text search on  Invitation
      *
      * @param string $term
      * @param string $lang
      *
      * @return array
      */
    public function textSearch($term, $lang)
    {
        return $this->invitationManager->textSearch($term, $lang);
    }

    /**
     * Performs multi-field grouped query on Invitation
     * @param array $searchCriteria
     * @param string $groupField
     * @param \Closure $groupValueProcessor
     * @return array
     */
    public function getAndGroupBy(array $searchCriteria, $groupFields = [], $valueProcessors = [])
    {
        return $this->invitationManager->getAndGroupBy($searchCriteria, $groupFields, $valueProcessors);
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
            ->setMessageParameters([
                'user' => $user,
                'email' => $invitation->getEmail(),
                'invitation' => $invitation->getId(),
                'link' => $this->router->generate('angular_endPoint', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ]);

        $this->mailer->send($mail, true);
    }
}
