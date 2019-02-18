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

class TemplateController extends Controller
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
    public function newTemplateAction(Request $request, TemplateService $templateService)
    {
        // Only super Admin can do this
        $this->denyAccessUnlessGranted([User::ROLE_SUPER_ADMIN]);

        $data = $request->getContent();
        $successful = $templateService->newTemplate($data);

        return $this->renderResponse($successful);
    }

    /**
     * @Route("/list")
     *
     * @param Request $request
     * @param TemplateService $templateService
     *
     * @return Response
     */
    public function listTemplatesAction(Request $request, TemplateService $templateService)
    {
        // Only super Admin can do this
        $this->denyAccessUnlessGranted([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);

        return $this->renderResponse($templateService->listTemplates());
    }
}
