<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\AccessLevel;
use AppBundle\Exception\DuplicateApplicationNameException;
use AppBundle\Manager\ApplicationManager;
use AppBundle\Manager\AccessLevelManager;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Service class for AccessLevel entities
 *
 */
class AccessLevelService
{
    /**
     * @var ApplicationManager
     */
    private $applicationManager;

    /**
     * @var AccessLevelManager
     */

    private $accessLevelManager;

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
     * @param ApplicationManager $applicationManager
     * @param AccessLevelManager $accessLevelManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ApplicationManager $applicationManager,
        AccessLevelManager $accessLevelManager,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->applicationManager = $applicationManager;
        $this->accessLevelManager = $accessLevelManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }


    public function list()
    {
        $accessLevels = $this->accessLevelManager->getAll();

        return $accessLevels;
    }

    public function add($json)
    {
        $accessLevel = $this
            ->serializer
            ->deserialize($json, AccessLevel::class, 'json');

        $id = $this->accessLevelManager->update($accessLevel);

        return $id;
    }

    public function update($json)
    {
        $context = new DeserializationContext();
        $context->setGroups(['minimalist']);
        $accessLevel = $this->serializer->deserialize($json, AccessLevel::class, 'json', $context);
        $this->accessLevelManager->update($accessLevel);

    }

    /**
     * @param string $id
     */
    public function delete($id)
    {
        $accessLevel = $this->accessLevelManager->getOneBy(['id' => $id]);
        if ($accessLevel === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "AccessLevel not Found");
        } else {
            return $this->accessLevelManager->delete($accessLevel);
        }

    }

     /**
     * @param string $id
     */
    public function getById($id)
    {
        $accessLevel = $this->accessLevelManager->getOneBy(['id' => $id]);
        if ($accessLevel === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "AccessLevel not Found");
        } else {
            return $accessLevel;
        }
    }

    /**
     * Get all accessLevels
     *
     * @return array
     */
    public function getAll()
    {
        return $this->accessLevelManager->getAll();
    }

}
