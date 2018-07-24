<?php declare (strict_types = 1);

namespace ATS\ClientBootstrapperBundle\Controller;

use ATS\ClientBootstrapperBundle\Service\ApplicationContextService;
use ATS\CoreBundle\Annotation\ApplicationView;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use Doctrine\Common\Annotations\AnnotationReader;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\RestBundle\Controller\Annotations\Route;
use Phramz\Doctrine\Annotation\Scanner\Scanner;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class ApplicationContextController extends BaseRestController
{
    /**
     * @var string
     */
    private $lookupDir;

    /**
     * @param KernelInterface $kernel
     */

    public function __construct(KernelInterface $kernel)
    {
        $this->lookupDir = $kernel->getRootDir() . '/../src';
    }

    /**
     * @Route("/__context", methods="GET")
     *
     * @param Request $request
     *
     * @return Response
     */

    public function getViewNamesAction(
        Request $request,
        ApplicationContextService $applicationContextService,
        ClientManagerInterface $clientManager
    ) {
        $payload = [];

        $reader = new AnnotationReader();
        $scanner = new Scanner($reader);
        $views = $scanner
            ->scan([ApplicationView::class])
            ->in($this->lookupDir)
        ;

        $payload = $applicationContextService->handleViews(iterator_to_array($views));

        $client = $clientManager->findClientBy(['allowedGrantTypes' => 'password']);

        if (!$client) {
            $client = $clientManager->createClient();
            $client->setAllowedGrantTypes(['password']);
            $clientManager->updateClient($client);
        }

        $authInfo = ['public' => $client->getPublicId(), 'secret' => $client->getSecret()];
        $payload['auth'] = $authInfo;

        $appDomain = $this->getParameter('app_domain');
        $appScheme = $this->getParameter('app_schemes');

        $payload['backendUrl'] = sprintf('%s://%s', $appScheme, $appDomain);

        return $this->prepareJsonResponse($payload);
    }
}
