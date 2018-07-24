<?php declare(strict_types=1);

namespace AppBundle\Service;

use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use AppBundle\Manager\InvitationManager;
use AppBundle\Document\Invitation;

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
     * Constructor
     *
     * @param InvitationManager $invitationManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(InvitationManager $invitationManager, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->invitationManager = $invitationManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
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
     * @return bool
     */
    public function newInvitation($json)
    {
        $invitation = $this
                ->serializer
                ->deserialize($json, Invitation::class, 'json');

        return $this->updateInvitation($json);
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
            $this->invitationManager->update($invitation);
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
}
