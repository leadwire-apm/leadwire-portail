<?php declare(strict_types=1);

namespace AppBundle\Controller\Rest;

use ATS\CoreBundle\Controller\Rest\BaseRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ATS\CoreBundle\Service\Voter\AclVoter;
use ATS\CoreBundle\HTTPFoundation\CsvResponse;
use ATS\CoreBundle\Service\Exporter\Exporter;
use AppBundle\Service\ApplicationTypeService;

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
        $this->denyAccessUnlessGranted(AclVoter::VIEW, $data);

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
        $this->denyAccessUnlessGranted(AclVoter::VIEW_ALL, ApplicationType::class);
        $data = $applicationtypeService->listApplicationTypes();

        return $this->prepareJsonResponse($data);
    }

    /**
     * @Route(
     *    "/paginate/{pageNumber}/{itemsPerPage}",
     *    methods="GET",
     *    defaults={"pageNumber" = 1, "itemsPerPage" = 20}
     * )
     *
     * @param Request $request
     * @param ApplicationTypeService $applicationtypeService
     * @param int $pageNumber
     * @param int $itemsPerPage
     *
     * @return Response
     */
    public function paginateApplicationTypesAction(
        Request $request,
        ApplicationTypeService $applicationtypeService,
        $pageNumber,
        $itemsPerPage
    ) {
        $this->denyAccessUnlessGranted(AclVoter::VIEW_ALL, ApplicationType::class);
        $pageResult = $applicationtypeService->paginate($pageNumber, $itemsPerPage);

        return $this->prepareJsonResponse($pageResult);
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
        $this->denyAccessUnlessGranted(AclVoter::CREATE, ApplicationType::class);
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
        $this->denyAccessUnlessGranted(AclVoter::DELETE, ApplicationType::class);
        $applicationtypeService->deleteApplicationType($id);

        return $this->prepareJsonResponse([]);
    }

    /**
     * @Route("/{lang}/{term}/search", methods="GET", defaults={"lang" = "en"})
     *
     * @param Request $request
     * @param ApplicationTypeService $applicationtypeService
     * @param string $term
     * @param string $lang
     *
     * @return Response
     */
    public function searchApplicationTypeAction(Request $request, ApplicationTypeService $applicationtypeService, $term, $lang)
    {
        $this->denyAccessUnlessGranted(AclVoter::SEARCH, ApplicationType::class);

        try {
            $result = $todoService->textSearch($term, $lang);
        } catch (\MongoException $e) {
            throw new BadRequestHttpException("Entity " . ApplicationType::class . " is not searchable. ");
        }

        return $this->prepareJsonResponse($applicationtypeService->textSearch($term, $lang));
    }

    /**
     * @Route("/csv-export", methods="POST")
     *
     * @param Request $request
     * @param Exporter $exporter
     *
     * @return CsvResponse
     */
    public function generateCsvExportAction(Request $request, Exporter $exporter)
    {
        $this->denyAccessUnlessGranted(AclVoter::EXPORT, ApplicationType::class);
        $data = json_decode($request->getContent(), true);

        $exported = $exporter
            ->setFormat(Exporter::FORMAT_CSV)
            ->setEntity(ApplicationType::class)
            ->setFilter($data['filter'])
            ->setSchema(explode(',', $data['schema']))
            ->export()
            ->getRawData()
        ;

        return new CsvResponse($exported);
    }
}
