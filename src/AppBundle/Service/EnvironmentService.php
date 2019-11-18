<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Exception\DuplicateApplicationNameException;

use AppBundle\Manager\ApplicationManager;
use AppBundle\Manager\EnvironmentManager;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Service class for Environment entities
 *
 */
class EnvironmentService
{
    /**
     * @var ApplicationManager
     */
    private $applicationManager;

    /**
     * @var EnvironmentManager
     */

    private $environmentManager;

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
     * @param EnvironmentManager $environmentManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ApplicationManager $applicationManager,
        EnvironmentManager $environmentManager,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->applicationManager = $applicationManager;
        $this->environmentManager = $environmentManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }


    public function list()
    {
        $environments = $this->environmentManager->getAll();

        return $environments;
    }

    public function add($json)
    {
        $environment = $this
            ->serializer
            ->deserialize($json, Environment::class, 'json');

        $id = $this->environmentManager->update($environments);

        return $id;
    }

    public function update($json)
    {
        $environment = $this->serializer->deserialize($json, Environment::class, 'json');
        $this->environmentManager->update($environment);

    }

    /**
     * @param string $id
     */
    public function delete($id)
    {
        $environment = $this->environmentManager->getOneBy(['id' => $id]);
        if ($environment === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "Environment not Found");
        } else {
            return $this->environmentManager->delete($environment);
        }

    }

     /**
     * @param string $id
     */
    public function getById($id)
    {
        $environment = $this->environmentManager->getOneBy(['id' => $id]);
        if ($environment === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "Environment not Found");
        } else {
            return $environment;
        }

    }

}
