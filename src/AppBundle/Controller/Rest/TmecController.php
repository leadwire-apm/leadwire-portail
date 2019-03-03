<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Document\User;
use AppBundle\Service\TemplateService;
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
     * @param TemplateService $templateService
     *
     * @return Response
     */
    public function newTmecAction(Request $request, TemplateService $templateService)
    {
    }

    /**
     * @Route("/{id}/update", methods="PUT")
     *
     * @param Request $request
     * @param TemplateService $templateService
     *
     * @return Response
     */
    public function updateTmecAction(Request $request, TemplateService $templateService)
    {
        // Only super Admin can do this
    }

    /**
     * @Route("/list")
     *
     * @param Request $request
     * @param TemplateService $templateService
     *
     * @return Response
     */
    public function listTmecAction(Request $request, TemplateService $templateService)
    {

    }

}
