<?php declare (strict_types = 1);

namespace ATS\TranslationBundle\Controller\Rest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ATS\TranslationBundle\Service\TranslationEntryService;

class TranslationEntryController extends Controller
{
    use RestControllerTrait;
    /**
     * @Route("/compact", methods="GET")
     *
     * @param Request $request
     * @param TranslationEntryService $translationEntryService
     *
     * @return Response
     */

    public function getCompactTranslationsAction(Request $request, TranslationEntryService $translationEntryService)
    {
        $data = $translationEntryService->listCompactTranslationEntries();

        return $this->renderResponse($data);
    }

    /**
     * @Route("/{id}/get", methods="GET")
     *
     * @param Request $request
     * @param TranslationEntryService $translationEntryService
     * @param string  $id
     *
     * @return Response
     */
    public function getTranslationEntryAction(Request $request, TranslationEntryService $translationEntryService, $id)
    {
        $data = $translationEntryService->getTranslationEntry($id);

        return $this->renderResponse($data);
    }

    /**
     * @Route("/list", methods="GET")
     *
     * @param Request $request
     * @param TranslationEntryService $translationEntryService
     *
     * @return Response
     */
    public function listTranslationEntriesAction(Request $request, TranslationEntryService $translationEntryService)
    {
        $data = $translationEntryService->listTranslationEntries();

        return $this->renderResponse($data);
    }

    /**
     * @Route("/new", methods="POST")
     *
     * @param Request $request
     * @param TranslationEntryService $translationEntryService
     *
     * @return Response
     */
    public function newTranslationEntryAction(Request $request, TranslationEntryService $translationEntryService)
    {
        $data = $request->getContent();

        $successful = $translationEntryService->newTranslationEntry($data);

        return $this->renderResponse($successful);
    }

    /**
     * @Route("/{id}/update", methods="PUT")
     *
     * @param Request $request
     * @param TranslationEntryService $translationEntryService
     *
     * @return Response
     */
    public function updateTranslationEntryAction(Request $request, TranslationEntryService $translationEntryService)
    {
        $data = $request->getContent();
        $successful = $translationEntryService->updateTranslationEntry($data);

        return $this->renderResponse($successful);
    }

    /**
     * @Route("/{id}/delete", methods="DELETE")
     *
     * @param Request $request
     * @param TranslationEntryService $translationEntryService
     * @param string $id
     *
     * @return Response
     */
    public function deleteTranslationEntryAction(
        Request $request,
        TranslationEntryService $translationEntryService,
        $id
    ) {

        $translationEntryService->deleteById($id);

        return $this->renderResponse([]);
    }

    /**
     * @Route("/get/{language}", name="translation_rest_get", methods="GET")
     *
     * @param Request $request
     * @param TranslationEntryService $translationEntryService
     * @param string $language
     *
     * @return JsonResponse
     */
    public function getByLanguageAction(Request $request, TranslationEntryService $translationEntryService, $language)
    {
        return new JsonResponse($translationEntryService->getByLanguage($language), Response::HTTP_OK);
    }

    /**
     * @Route("/get-available-languages", methods="GET")
     *
     * @param Request $request
     * @param TranslationEntryService $translationEntryService
     *
     * @return JsonResponse
     */
    public function getAvailableLanguagesAction(Request $request, TranslationEntryService $translationEntryService)
    {
        return new JsonResponse($translationEntryService->getAvailableLanguages(), Response::HTTP_OK);
    }

    /**
     * @Route("/new-language", methods="POST")
     *
     * @param Request $request
     * @param TranslationEntryService $translationEntryService
     *
     * @return JsonResponse
     */
    public function addNewLanguageAction(Request $request, TranslationEntryService $translationEntryService)
    {
        $status = $translationEntryService->addNewLanguage($request->get('language'));

        return new JsonResponse($status, Response::HTTP_OK);
    }

    /**
     * @Route("/keys/init", methods="POST")
     *
     * @param Request $request
     * @param TranslationEntryService $translationEntryService
     *
     * @return JsonResponse
     */

    public function initKeysAction(Request $request, TranslationEntryService $translationEntryService)
    {
        $data = json_decode($request->getContent());
        $translationEntryService->initKeys($data->keys);

        return new JsonResponse([], Response::HTTP_OK);
    }
}
