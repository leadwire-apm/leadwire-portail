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
        $tmec = $this->tmecManager->getOneBy(['version' => $params['version']]);

        if ($tmec === null) {
            $tmec = $this->tmecManager->create($params);
        } else {
            throw new AccessDeniedHttpException("Version is already exist");
        }
        return $tmec;
    }
}
