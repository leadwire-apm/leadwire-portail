<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Tmec;
use Symfony\Component\Finder\Finder;
use AppBundle\Manager\TmecManager;
use AppBundle\Service\StepService;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use AppBundle\Manager\ApplicationManager;
use AppBundle\Document\Application;

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
        $tmec = $this->tmecManager->getTmecByVersion($params['version'], $params['applicationId']);

        if ($tmec === null) {
            $tmec = $this->tmecManager->create(
                $params['version'],
                $params['description'],
                $params['startDate'],
                $params['endDate'],
                $params['applicationId']);

            $this->stepService->initSteps($tmec);
        } else {
            throw new AccessDeniedHttpException("Version is already exist");
        }
        return $tmec;
    }


    public function update($json)
    {
        /** @var Tmec $tmec */
        $tmec = $this->serializer->deserialize($json, Tmec::class, 'json');
        $this->tmecManager->update($tmec);
    }

    /**
     * @param array $params
     */
    public function listTmec(array $params)
    {
        $applications =  $this->applicationManager->getBy([]);

        $ids = array();

        foreach($applications as $application)
        {
             array_push($ids, $application->getId());
        }

        return new JsonResponse($output);

        $tmecList = $this->tmecManager->getTmecByApplication($params['completed'], $ids);
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
        $applications =  $this->applicationManager->getBy([]);
        return $applications;
    }
}
