<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Document\User;
use AppBundle\Service\TmecService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TmecController extends Controller
{

    use RestControllerTrait;

    /**
     * @Route("/new", methods="POST")
     *
     * @param Request $request
     * @param TmecService $tmecService
     *
     * @return Response
     */
    public function newTmecAction(Request $request, TmecService $tmecService)
    {
        $data = json_decode($request->getContent(), true);
        $tmec = $tmecService->newTmec($data);
        return $this->renderResponse($tmec);
    }

    /**
     * @Route("/{id}/update", methods="PUT")
     *
     * @param Request $request
     * @param TmecService $tmecService
     *
     * @return Response
     */
    public function updateTmecAction(Request $request, TmecService $tmecService)
    {
        // Only super Admin can do this
    }

    /**
     * @Route("/list/{application}", methods="GET")
     *
     * @param Request $request
     * @param TmecService $tmecService
     * @param string $application
     * 
     * @return Response
     */
    public function listTmecAction(Request $request, TmecService $tmecService, $application)
    {
        $tmec = $tmecService->listTmec($application);
        return $this->renderResponse($tmec);
    }

}
