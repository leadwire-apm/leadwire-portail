<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\ApplicationTypeService;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationTypeController extends BaseRestController
{
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

        return $this->prepareJsonResponse($data);
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

        return $this->prepareJsonResponse($data);
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
        $data = $request->getContent();
        $successful = $applicationtypeService->newApplicationType($data);

        return $this->prepareJsonResponse($successful);
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

        return $this->prepareJsonResponse($successful);
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

        return $this->prepareJsonResponse(null);
    }
}
