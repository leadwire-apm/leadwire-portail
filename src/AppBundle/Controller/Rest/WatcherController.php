<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\ApplicationService;
use AppBundle\Service\WatcherService;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use AppBundle\Exception\DuplicateApplicationNameException;
use AppBundle\Document\AccessLevel;

class WatcherController extends Controller
{
    use RestControllerTrait;

    public function hasUserPermission(ApplicationService $applicationService, $app, $envId){
        $user = $this->getUser();
        if($applicationService->userHasPermission(
            $app, 
            $user, 
            $envId, 
            array(AccessLevel::ADMIN, AccessLevel::EDITOR))){
            return true;
        } else {
            return false;
        }
    }

    /**
     * @Route("/add", methods="POST")
     *
     * @param Request        $request
     * @param WatcherService $watcherService
     * @param ApplicationService $applicationService
     *
     * @return Response
     */
    public function saveOrUpdateAction(
        Request $request, 
        WatcherService $watcherService,
        ApplicationService $applicationService) {
        try {
            $data = $request->getContent();
            $_data = json_decode($data, true);
            if($this->hasUserPermission($applicationService, $_data["appId"], $_data["envId"])){
                $watcher = $watcherService->saveOrUpdate($data);
                return $this->renderResponse($watcher, Response::HTTP_OK, []);
            } else {
                return $this->exception(['message' => "You dont have rights permissions"], 400);
            }
        }
        catch (DuplicateApplicationNameException $e) {
            return $this->renderResponse(['message' => $e->getMessage()], Response::HTTP_NOT_ACCEPTABLE);
        }
         catch (\Exception $e) {
            return $this->exception($e->getMessage(), 400);
        }
    }

    /**
     * @Route("/list", methods="POST")
     *
     * @param Request        $request
     * @param WatcherService $watcherService
     * @param ApplicationService $applicationService
     *
     * @return Response
     */
    public function listAction(
        Request $request,
        WatcherService $watcherService,
        ApplicationService $applicationService) {
        try {
            $data = $request->getContent();
            $_data = \json_decode($data, true);
            if($this->hasUserPermission($applicationService, $_data["appId"], $_data["envId"])){
                $watcher = $watcherService->list(json_decode($data, true));
                return $this->renderResponse($watcher, Response::HTTP_OK, []);
            } else {
                return $this->exception(['message' => "You dont have rights permissions"], 400);
            }
        }
         catch (\Exception $e) {
            return $this->exception($e->getMessage(), 400);
        }
    }

    /**
     * @Route("/{id}/delete", methods="POST")
     *
     * @param Request        $request
     * @param WatcherService $watcherService
     * @param ApplicationService $applicationService
     * @param string $id
     *
     * @return Response
     */
    public function deleteAction(
        Request $request, 
        WatcherService $watcherService,
        ApplicationService $applicationService,
        $id) {
        try {
            $data = $request->getContent();
            $_data = \json_decode($data, true);
            if($this->hasUserPermission($applicationService, $_data["appId"], $_data["envId"])){
                $watcher = $watcherService->delete($id);
                return $this->renderResponse($watcher, Response::HTTP_OK, []);
            } else {
                return $this->exception(['message' => "You dont have rights permissions"], 400);
            }
        }
         catch (\Exception $e) {
            return $this->exception($e->getMessage(), 400);
        }
    }

    /**
     * @Route("/{id}/execute", methods="POST")
     *
     * @param Request        $request
     * @param WatcherService $watcherService
     * @param ApplicationService $applicationService
     * @param string $id
     *
     * @return Response
     */
    public function executeAction(
        Request $request, 
        WatcherService $watcherService,
        ApplicationService $applicationService,
         $id) {
        try {
            $data = $request->getContent();
            $_data = \json_decode($data, true);
            if($this->hasUserPermission($applicationService, $_data["appId"], $_data["envId"])){
                $watcher = $watcherService->execute($id);
                return $this->renderResponse($watcher, Response::HTTP_OK, []);
            } else {
                return $this->exception(['message' => "You dont have rights permissions"], 400);
            }

        }
         catch (\Exception $e) {
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
    private function exception($message, $status = 400) {
        try {
            return $this->renderResponse(array('message' => $message), $status);
        }
         catch (\Exception $e) {
            return $this->exception($e->getMessage(), 400);
        }
    }
    
}
