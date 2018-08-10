<?php declare(strict_types=1);

namespace ATS\ScheduledTaskBundle\Controller\Rest;

use ATS\CoreBundle\Controller\Rest\BaseRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ATS\ScheduledTaskBundle\Service\TaskService;

class TaskController extends BaseRestController
{
    /**
     * @Route("/{id}/get", methods="GET")
     *
     * @param Request $request
     * @param TaskService $taskService
     * @param string  $id
     *
     * @return Response
     */
    public function getTaskAction(Request $request, TaskService $taskService, $id)
    {
        $data = $taskService->getTask($id);

        return $this->prepareJsonResponse($data);
    }

    /**
     * @Route("/list", methods="GET")
     *
     * @param Request $request
     * @param TaskService $taskService
     *
     * @return Response
     */
    public function listTasksAction(Request $request, TaskService $taskService)
    {
        $data = $taskService->listTasks();

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
     * @param TaskService $taskService
     * @param int $pageNumber
     * @param int $itemsPerPage
     *
     * @return Response
     */
    public function paginateTasksAction(
        Request $request,
        TaskService $taskService,
        $pageNumber,
        $itemsPerPage
    ) {
        $pageResult = $taskService->paginate($pageNumber, $itemsPerPage);

        return $this->prepareJsonResponse($pageResult);
    }

    /**
     * @Route("/new", methods="POST")
     *
     * @param Request $request
     * @param TaskService $taskService
     *
     * @return Response
     */
    public function newTaskAction(Request $request, TaskService $taskService)
    {
        $data = $request->getContent();
        $successful = $taskService->newTask($data);

        return $this->prepareJsonResponse($successful);
    }

    /**
    * @Route("/{id}/update", methods="PUT")
    *
    * @param Request $request
    * @param TaskService $taskService
    *
    * @return Response
    */
    public function updateTaskAction(Request $request, TaskService $taskService)
    {
        $data = $request->getContent();
        $successful = $taskService->updateTask($data);

        return $this->prepareJsonResponse($successful);
    }

    /**
     * @Route("/{id}/delete", methods="DELETE")
     *
     * @param Request $request
     * @param TaskService $taskService
     * @param string $id
     *
     * @return Response
     */
    public function deleteTaskAction(Request $request, TaskService $taskService, $id)
    {
        $taskService->deleteTask($id);

        return $this->prepareJsonResponse([]);
    }

     /**
      * @Route("/{term}/{lang}/search", methods="GET", defaults={"lang" = "en"})
      *
      * @param Request $request
      * @param TaskService $taskService
      * @param string $term
      * @param string $lang
      *
      * @return Response
      */
    public function searchTaskAction(Request $request, TaskService $taskService, $term, $lang)
    {
        return $this->prepareJsonResponse($taskService->textSearch($term, $lang));
    }
}
