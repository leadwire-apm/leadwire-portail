<?php declare(strict_types=1);

namespace ATS\CoreBundle\Controller\Rest;

use ATS\CoreBundle\Event\FileUploadedEvent;
use Symfony\Component\Filesystem\Filesystem;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class RestFileOperationController extends Controller
{
    /**
     * @Route("/upload", name="core_rest_upload", methods="POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {
        $responseStatus = Response::HTTP_NO_CONTENT;
        $responseData = [];

        try {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $request->files->get('file');
            $entity = ucfirst($request->get('entity'));

            if ($uploadedFile) {
                $fileExtension = $uploadedFile->guessExtension();
                $dataPath = $this->container->getParameter('uploads_dir');
                $now = new \DateTime();
                $newFileName = md5($now->format('Y-m-d-h-i-s'));
                $uploadedFile->move($dataPath, "$newFileName.$fileExtension");

                $eventDispatcher = $this->container->get('event_dispatcher');
                $event = new FileUploadedEvent("$dataPath/$entity/$newFileName.$fileExtension");
                $eventDispatcher->dispatch(FileUploadedEvent::NAME, $event);

                $responseStatus = Response::HTTP_CREATED;

                $responseData = ['name' => "$newFileName.$fileExtension"];
            }
        } catch (FileException $exception) {
            $responseData = ['error' => $exception->getMessage()];
            $responseStatus = Response::HTTP_BAD_REQUEST;
        } finally {
            return new JsonResponse($responseData, $responseStatus);
        }
    }

    /**
     * @Route("/download/{resourceName}", name="core_rest_download", methods="GET")
     *
     * @param Request $request
     *
     * @return BinaryFileResponse
     */
    public function downloadAction(Request $request, $resourceName)
    {
        $downloadsRoot = $this->container->getParameter('downloads_dir');
        $filePath = "$downloadsRoot/$resourceName";

        $fs = new FileSystem();

        if (!$fs->exists($filePath)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        return new BinaryFileResponse($filePath);
    }

    /**
     * @Route("/resource/{resourceName}", name="core_rest_resource", methods="GET")
     *
     * @param Request $request
     *
     * @return BinaryFileResponse
     */
    public function secureAction(Request $request, $resourceName)
    {
        $resourceName = str_replace('-', '.', $resourceName);
        $downloadsRoot = $this->container->getParameter('uploads_dir');
        $filePath = "$downloadsRoot/$resourceName";

        $fs = new FileSystem();

        if (!$fs->exists($filePath)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        return new BinaryFileResponse($filePath);
    }
}
