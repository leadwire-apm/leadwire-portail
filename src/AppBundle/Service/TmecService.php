<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Tmec;
use Symfony\Component\Finder\Finder;
use AppBundle\Manager\TmecManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TmecService
{
    /**
     * @var TmecManager
     */
    private $tmecManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        TmecManager $tmecManager,
        SerializerInterface $serializer
    ) {
        $this->tmecManager = $tmecManager;
        $this->serializer = $serializer;
    }

    /**
     * @param array $params
     */
    public function newTmec(array $params)
    {
        $tmec = $this->tmecManager->getTmecByVersion($params['version']);

        if ($tmec === null) {
            $tmec = $this->tmecManager->create(
                $params['version'],
                $params['description'],
                $params['startDate'],
                $params['endDate'],
                $params['applicationId']);
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
        $tmecList = $this->tmecManager->getTmecByApplication($params['application']);
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
}
