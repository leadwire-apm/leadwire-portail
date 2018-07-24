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
use AppBundle\Service\AppService;

class AppController extends BaseRestController
{

    public function isNotPublic()
    {
        return true;
    }

    /**
     * @Route("/{id}/get", methods="GET")
     *
     * @param Request $request
     * @param AppService $appService
     * @param string  $id
     *
     * @return Response
     */
    public function getAppAction(Request $request, AppService $appService, $id)
    {
        $data = $appService->getApp($id);
        //$this->denyAccessUnlessGranted(AclVoter::VIEW, $data);

        return $this->prepareJsonResponse($data);
    }

    /**
     * @Route("/list", methods="GET")
     *
     * @param Request $request
     * @param AppService $appService
     *
     * @return Response
     */
    public function listAppsAction(Request $request, AppService $appService)
    {
        //$this->denyAccessUnlessGranted(AclVoter::VIEW_ALL, App::class);
        $data = $appService->listApps();

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
     * @param AppService $appService
     * @param int $pageNumber
     * @param int $itemsPerPage
     *
     * @return Response
     */
    public function paginateAppsAction(
        Request $request,
        AppService $appService,
        $pageNumber,
        $itemsPerPage
    ) {
        $this->denyAccessUnlessGranted(AclVoter::VIEW_ALL, App::class);
        $pageResult = $appService->paginate($pageNumber, $itemsPerPage);

        return $this->prepareJsonResponse($pageResult);
    }

    /**
     * @Route("/new", methods="POST")
     *
     * @param Request $request
     * @param AppService $appService
     *
     * @return Response
     */
    public function newAppAction(Request $request, AppService $appService)
    {
        $this->denyAccessUnlessGranted(AclVoter::CREATE, App::class);
        $data = $request->getContent();
        $successful = $appService->newApp($data);

        return $this->prepareJsonResponse($successful);
    }

    /**
    * @Route("/{id}/update", methods="PUT")
    *
    * @param Request $request
    * @param AppService $appService
    *
    * @return Response
    */
    public function updateAppAction(Request $request, AppService $appService)
    {
        $data = $request->getContent();
        $successful = $appService->updateApp($data);

        return $this->prepareJsonResponse($successful);
    }

    /**
     * @Route("/{id}/delete", methods="DELETE")
     *
     * @param Request $request
     * @param AppService $appService
     * @param string $id
     *
     * @return Response
     */
    public function deleteAppAction(Request $request, AppService $appService, $id)
    {
        $this->denyAccessUnlessGranted(AclVoter::DELETE, App::class);
        $appService->deleteApp($id);

        return $this->prepareJsonResponse([]);
    }

    /**
     * @Route("/{lang}/{term}/search", methods="GET", defaults={"lang" = "en"})
     *
     * @param Request $request
     * @param AppService $appService
     * @param string $term
     * @param string $lang
     *
     * @return Response
     */
    public function searchAppAction(Request $request, AppService $appService, $term, $lang)
    {
        $this->denyAccessUnlessGranted(AclVoter::SEARCH, App::class);

        try {
            $result = $todoService->textSearch($term, $lang);
        } catch (\MongoException $e) {
            throw new BadRequestHttpException("Entity " . App::class . " is not searchable. ");
        }

        return $this->prepareJsonResponse($appService->textSearch($term, $lang));
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
        $this->denyAccessUnlessGranted(AclVoter::EXPORT, App::class);
        $data = json_decode($request->getContent(), true);

        $exported = $exporter
            ->setFormat(Exporter::FORMAT_CSV)
            ->setEntity(App::class)
            ->setFilter($data['filter'])
            ->setSchema(explode(',', $data['schema']))
            ->export()
            ->getRawData()
        ;

        return new CsvResponse($exported);
    }
}
