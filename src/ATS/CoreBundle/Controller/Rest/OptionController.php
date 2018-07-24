<?php declare(strict_types=1);

namespace ATS\CoreBundle\Controller\Rest;

use ATS\CoreBundle\Controller\Rest\BaseRestController;
use ATS\CoreBundle\Service\OptionService;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OptionController extends BaseRestController
{
    /**
     * @Route("/{id}/get", methods="GET")
     *
     * @param Request $request
     * @param OptionService $optionService
     * @param string  $id
     *
     * @return Response
     */
    public function getOptionAction(Request $request, OptionService $optionService, $id)
    {
        $data = $optionService->getOption($id);

        return $this->prepareJsonResponse($data);
    }

    /**
     * @Route("/list", methods="GET")
     *
     * @param Request $request
     * @param OptionService $optionService
     *
     * @return Response
     */
    public function listOptionsAction(Request $request, OptionService $optionService)
    {
        $data = $optionService->listOptions();

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
     * @param OptionService $optionService
     * @param int $pageNumber
     * @param int $itemsPerPage
     *
     * @return Response
     */
    public function paginateOptionsAction(
        Request $request,
        OptionService $optionService,
        $pageNumber,
        $itemsPerPage
    ) {
        $pageResult = $optionService->paginate($pageNumber, $itemsPerPage);

        return $this->prepareJsonResponse($pageResult);
    }

    /**
     * @Route("/new", methods="POST")
     *
     * @param Request $request
     * @param OptionService $optionService
     *
     * @return Response
     */
    public function newOptionAction(Request $request, OptionService $optionService)
    {
        $data = $request->getContent();
        $successful = $optionService->newOption($data);

        return $this->prepareJsonResponse($successful);
    }

    /**
     * @Route("/{id}/update", methods="PUT")
     *
     * @param Request $request
     * @param OptionService $optionService
     *
     * @return Response
     */
    public function updateOptionAction(Request $request, OptionService $optionService)
    {
        $data = $request->getContent();
        $successful = $optionService->updateOption($data);

        return $this->prepareJsonResponse($successful);
    }

    /**
     * @Route("/{id}/delete", methods="DELETE")
     *
     * @param Request $request
     * @param OptionService $optionService
     * @param string $id
     *
     * @return Response
     */
    public function deleteOptionAction(Request $request, OptionService $optionService, $id)
    {
        $optionService->deleteOption($id);

        return $this->prepareJsonResponse([]);
    }

    /**
     * @Route("/{term}/search", methods="GET")
     *
     * @param Request $request
     * @param OptionService $optionService
     * @param string $term
     *
     * @return Response
     */
    public function searchOptionAction(Request $request, OptionService $optionService, $term)
    {
        return $this->prepareJsonResponse($optionService->textSearch($term));
    }
}
