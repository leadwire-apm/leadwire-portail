<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Exception\DuplicateApplicationTypeException;
use AppBundle\Service\ApplicationTypeService;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Service\TemplateService;

class ApplicationTypeController extends Controller
{
    use RestControllerTrait;
    /**
     * @Route("/{id}/get", methods="GET")
     *
     * @param Request $request
     * @param ApplicationTypeService $applicationtypeService
     * @param string  $id
     *
     * @return Response
     */
    public function getApplicationTypeAction(Request $request, ApplicationTypeService $applicationtypeService, $id)
    {
        $data = $applicationtypeService->getApplicationType($id);

        return $this->renderResponse($data);
    }

    /**
     * @Route("/list", methods="GET")
     *
     * @param Request $request
     * @param ApplicationTypeService $applicationtypeService
     *
     * @return Response
     */
    public function listApplicationTypesAction(Request $request, ApplicationTypeService $applicationtypeService)
    {
        $data = $applicationtypeService->listApplicationTypes();

        return $this->renderResponse($data);
    }

    /**
     * @Route("/new", methods="POST")
     *
     * @param Request $request
     * @param ApplicationTypeService $applicationtypeService
     *
     * @return Response
     */
    public function newApplicationTypeAction(Request $request, ApplicationTypeService $applicationtypeService)
    {
        try {
            $data = $request->getContent();
            $successful = $applicationtypeService->newApplicationType($data);

            return $this->renderResponse($successful);
        } catch (DuplicateApplicationTypeException $e) {
            return $this->renderResponse(['message' => $e->getMessage()], Response::HTTP_NOT_ACCEPTABLE);
        }
    }

    /**
     * @Route("/{id}/update", methods="PUT")
     *
     * @param Request $request
     * @param ApplicationTypeService $applicationtypeService
     *
     * @return Response
     */
    public function updateApplicationTypeAction(Request $request, ApplicationTypeService $applicationtypeService)
    {
        $data = $request->getContent();
        $successful = $applicationtypeService->updateApplicationType($data);

        return $this->renderResponse($successful);
    }

    /**
     * @Route("/{id}/delete", methods="DELETE")
     *
     * @param Request $request
     * @param ApplicationTypeService $applicationtypeService
     * @param string $id
     *
     * @return Response
     */
    public function deleteApplicationTypeAction(Request $request, ApplicationTypeService $applicationtypeService, $id)
    {
        $applicationtypeService->deleteApplicationType($id);

        return $this->renderResponse(null);
    }
}
