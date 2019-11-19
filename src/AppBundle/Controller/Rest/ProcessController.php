<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Document\Process;
use AppBundle\Service\ProcessService;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProcessController extends Controller
{

    use RestControllerTrait;

    /**
     * @Route("/get", methods="GET")
     *
     * @param Request        $request
     * @param ProcessService $processService
     *
     * @return Response
     */
    public function getProcessAction(Request $request, ProcessService $processService)
    {
        $user = $this->getUser();
        $process = $processService->getInProgressProcessByUser($user);

        return $this->renderResponse($process);
    }

    /**
     * @Route("/login/get", methods="GET")
     *
     * @param Request        $request
     * @param ProcessService $processService
     *
     * @return Response
     */
    public function getLoginProcessAction(Request $request, ProcessService $processService)
    {
        $process = $processService->getLoginProcess();

        return $this->renderResponse($process);
    }
}
