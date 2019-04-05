<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Document\Step;
use AppBundle\Service\StepService;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StepController extends Controller
{

    use RestControllerTrait;

    /**
     * @Route("/update", methods="POST")
     *
     * @param Request $request
     * @param StepService $stepService
     *
     * @return Response
     */
    public function updateStepAction(Request $request, StepService $stepService)
    {
        $data = $request->getContent();
        $successful = $stepService->updateStep($data);
        return $this->renderResponse($successful);
    }

    /**
     * @Route("/list/{compagne}", methods="GET")
     *
     * @param Request $request
     * @param StepService $stepService
     * @param string $compagne
     *
     * @return Response
     */
    public function listAction(Request $request, StepService $stepService, $compagne)
    {
        $params = ['compagne' => $compagne];
        $step = $stepService->list($params);
        return $this->renderResponse($step);
    }
}
