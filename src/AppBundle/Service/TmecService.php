<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Tmec;
use AppBundle\Manager\TmecManager;
use AppBundle\Service\StepService;
use AppBundle\Document\Application;
use JMS\Serializer\SerializerInterface;
use AppBundle\Manager\ApplicationManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TmecService
{
    /**
     * @var TmecManager
     */
    private $tmecManager;

    /**
     * @var StepService
     */
    private $stepService;

    /**
     * @var ApplicationManager
     */
    private $applicationManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        TmecManager $tmecManager,
        StepService $stepService,
        ApplicationManager $applicationManager,
        SerializerInterface $serializer
    ) {
        $this->tmecManager = $tmecManager;
        $this->stepService = $stepService;
        $this->applicationManager = $applicationManager;
        $this->serializer = $serializer;
    }

    /**
     * @param array $params
     */
    public function newTmec(array $params)
    {
        /** @var ?Tmec $tmec */
        $tmec = $this->tmecManager->getTmecByVersion($params['version'], $params['application']);

        if ($tmec === null) {
            $tmec = $this->tmecManager->create(
                $params['version'],
                $params['description'],
                $params['startDate'],
                $params['endDate'],
                $params['application'],
                $params['applicationName']
            );

            $this->stepService->initSteps($tmec);
        } else {
            throw new AccessDeniedHttpException("Version already exists");
        }

        return $tmec;
    }

    public function update($json)
    {
        /** @var Tmec $tmec */
        $tmec = $this->serializer->deserialize($json, Tmec::class, 'json');

        $dbDocument = $this->tmecManager->getOneBy(['id' => $tmec->getId()]);

        if ($dbDocument instanceof Tmec) {
            $dbDocument->setApplication($tmec->getApplication());
            $dbDocument->setVersion($tmec->getVersion());
            $dbDocument->setDescription($tmec->getDescription());
            $dbDocument->setStartDate($tmec->getStartDate());
            $dbDocument->setEndDate($tmec->getEndDate());
            $this->tmecManager->update($dbDocument);
        } else {
            throw new \Exception("Invalid document");
        }
    }

    /**
     * @param array $params
     */
    public function listTmec(array $params)
    {
        $tmecList = $this->tmecManager->getTmecByApplication($params['completed'], $params['ids']);

        return $tmecList;
    }

    /**
     * @param string $params
     */
    public function getTmec(string $params)
    {
        $tmec = $this->tmecManager->getTmecById($params);
        return $tmec;
    }

    /**
     * @param string $id
     *
     * @return \MongoId
     */
    public function delete(string $id)
    {
        $tmec = $this->tmecManager->getTmecById($id);
        if ($tmec === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "Template not Found");
        } else {
            return $this->tmecManager->delete($tmec);
        }
    }

    /**
     * Get specific apps
     *
     * @codeCoverageIgnore
     *
     * @return array
     */
    public function getApplications()
    {
        $applications = $this->applicationManager->getBy([]);

        return $applications;
    }
}
