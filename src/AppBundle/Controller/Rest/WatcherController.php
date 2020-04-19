<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\WatcherService;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WatcherController extends Controller
{
    use RestControllerTrait;

    /**
     * @Route("/add", methods="PUT")
     *
     * @param Request        $request
     * @param WatcherService $watcherService
     *
     * @return Response
     */
    public function addAction(Request $request, WatcherService $watcherService)
    {
        try {
            $data = $request->getContent();
            $watcher = $watcherService->add(json_decode($data, true));
            return $this->renderResponse($watcher, Response::HTTP_OK, []);
        } catch (\Exception $e) {
            return $this->exception($e->getMessage(), 400);
        }
    }


    /**
     * exception
     *
     * @param string  $message
     * @param integer $status
     *
     * @return Response
     */
    private function exception($message, $status = 400)
    {
        return $this->renderResponse(array('message' => $message), $status);
    }
}
