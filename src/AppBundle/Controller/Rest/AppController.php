<?php declare(strict_types=1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\AuthService;
use AppBundle\Service\ElasticSearch;
use AppBundle\Service\LdapService;
use AppBundle\Service\UserService;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ATS\CoreBundle\Service\Voter\AclVoter;
use ATS\CoreBundle\HTTPFoundation\CsvResponse;
use ATS\CoreBundle\Service\Exporter\Exporter;
use AppBundle\Service\AppService;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppController extends BaseRestController
{

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
        $this->denyAccessUnlessGranted(AclVoter::VIEW, $data);

        return $this->prepareJsonResponse($data, 200, "Default");
    }

    /**
     * @Route("/{id}/dashboards", methods="GET")
     *
     * @param Request $request
     * @param AppService $appService
     * @param string  $id
     *
     * @return Response
     */
    public function getDashboardsAction(Request $request, AppService $appService, ElasticSearch $elastic, $id)
    {
        $app = $appService->getApp($id);
        if (!$app) {
            throw new HttpException(404, "App not Found");
        } else {
            return $this->json($elastic->getDashboads($app));
        }
    }


    /**
     * @Route("/{id}/activate", methods="POST")
     *
     * @param Request $request
     * @param AppService $appService
     * @param string  $id
     *
     * @return Response
     */
    public function activationAppAction(Request $request, AppService $appService, $id)
    {
        $app = $appService->activateApp($id, json_decode($request->getContent()));
        //$this->denyAccessUnlessGranted(AclVoter::VIEW, $app);
        if (!!$app) {
            return $this->prepareJsonResponse($app, 200, "Default");
        } else {
            return $this->prepareJsonResponse($app, 400, "Default");
        }
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
        $this->denyAccessUnlessGranted(AclVoter::VIEW_ALL, App::class);
        $user = $this->getUser();
        $data = array_merge($appService->invitedListApps($user), $appService->listApps($user));
        return $this->prepareJsonResponse($data, 200, "Default");
    }

    /**
     * @Route("/invited/list", methods="GET")
     *
     * @param Request $request
     * @param AppService $appService
     *
     * @param AuthService $auth
     * @return Response
     */
    public function invitedListAppsAction(Request $request, AppService $appService)
    {
        $this->denyAccessUnlessGranted(AclVoter::VIEW_ALL, App::class);

        $data = $appService->invitedListApps($this->getUser());

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
     * @param AuthService $authService
     * @return Response
     * @throws \Exception
     */
    public function newAppAction(Request $request, AppService $appService)
    {
        $this->denyAccessUnlessGranted(AclVoter::CREATE, App::class);
        $data = $request->getContent();
        $successful = $appService->newApp($data, $this->getUser());

        return $this->prepareJsonResponse($successful != null ? $successful : false);
    }

    /**
     * @Route("/{id}/update", methods="PUT")
     *
     * @param Request $request
     * @param AppService $appService
     *
     * @param string $id
     * @return Response
     */
    public function updateAppAction(Request $request, AppService $appService, string $id)
    {
        $data = $request->getContent();
        $successful = $appService->updateApp($data, $id);

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

//    /**
//     * @Route("/{lang}/{term}/search", methods="GET", defaults={"lang" = "en"})
//     *
//     * @param Request $request
//     * @param AppService $appService
//     * @param string $term
//     * @param string $lang
//     *
//     * @return Response
//     */
//    public function searchAppAction(Request $request, AppService $appService, $term, $lang)
//    {
//        $this->denyAccessUnlessGranted(AclVoter::SEARCH, App::class);
//
//        try {
//            $result = $todoService->textSearch($term, $lang);
//        } catch (\MongoException $e) {
//            throw new BadRequestHttpException("Entity " . App::class . " is not searchable. ");
//        }
//
//        return $this->prepareJsonResponse($appService->textSearch($term, $lang));
//    }

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
