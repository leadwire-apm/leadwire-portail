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
     * @Route("/{id}/update", methods="PUT")
     *
     * @param Request $request
     * @param TemplateService $templateService
     *
     * @return Response
     */
    public function updateTemplateAction(Request $request, TemplateService $templateService)
    {
        // Only super Admin can do this
        $this->denyAccessUnlessGranted([User::ROLE_SUPER_ADMIN]);

        $data = $request->getContent();
        $successful = $templateService->updateTemplate($data);

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

    /**
     * @Route("/{id}/delete", methods="DELETE")
     *
     * @param Request $request
     * @param TemplateService $templateService
     * @param string $id
     *
     * @return Response
     */
    public function deleteTemplateAction(Request $request, TemplateService $templateService, string $id)
    {
        // Only super Admin can do this
        $this->denyAccessUnlessGranted([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);

        $templateService->deleteTemplate($id);

        return $this->renderResponse(null, Response::HTTP_OK);
    }

    /**
     * @Route("/{id}/get", methods="GET")
     *
     * @param Request $request
     * @param TemplateService $templateService
     * @param string  $id
     *
     * @return Response
     */
    public function getApplicationAction(Request $request, TemplateService $templateService, $id)
    {
        $data = $templateService->getTemplate($id);

        return $this->renderResponse($data, Response::HTTP_OK, ["Default"]);
    }
}
