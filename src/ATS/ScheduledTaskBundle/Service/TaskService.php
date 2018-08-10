<?php declare(strict_types=1);

namespace ATS\ScheduledTaskBundle\Service;

use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use ATS\ScheduledTaskBundle\Manager\TaskManager;
use ATS\ScheduledTaskBundle\Document\Task;

/**
 * Service class for Task entities
 *
 */
class TaskService
{
    /**
     * @var TaskManager
     */
    private $taskManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param TaskManager $taskManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(TaskManager $taskManager, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->taskManager = $taskManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * List all tasks
     *
     * @return array
     */
    public function listTasks()
    {
        return $this->taskManager->getAll();
    }

    /**
     * Paginates through Tasks
     *
     * @param int $pageNumber
     * @param int $itemsPerPage
     * @param array $criteria
     *
     * @return array
     */
    public function paginate($pageNumber = 1, $itemsPerPage = 20, array $criteria = [])
    {
        return $this->taskManager->paginate($criteria, $pageNumber, $itemsPerPage);
    }

    /**
     * Get a specific task
     *
     * @param string $id
     *
     * @return Task
     */
    public function getTask($id)
    {
         return $this->taskManager->getOneBy(['id' => $id]);
    }

    /**
     * Creates a new task from JSON data
     *
     * @param string $json
     *
     * @return bool
     */
    public function newTask($json)
    {
        $task = $this
                ->serializer
                ->deserialize($json, Task::class, 'json');

        return $this->updateTask($json);
    }

    /**
     * Updates a specific task from JSON data
     *
     * @param string $json
     *
     * @return bool
     */
    public function updateTask($json)
    {
        $isSuccessful = false;

        try {
            $task = $this->serializer->deserialize($json, Task::class, 'json');
            $this->taskManager->update($task);
            $isSuccessful = true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $isSuccessful = false;
        }

        return $isSuccessful;
    }

    public function update($task)
    {
        return $this->taskManager->update($task);
    }

    /**
     * Deletes a specific task from JSON data
     *
     * @param string $id
     *
     * @return void
     */
    public function deleteTask($id)
    {
         $this->taskManager->deleteById($id);
    }

     /**
      * Performs a full text search on  Task
      *
      * @param string $term
      * @param string $lang
      *
      * @return array
      */
    public function textSearch($term, $lang)
    {
        return $this->taskManager->textSearch($term, $lang);
    }

    public function getActive()
    {
        return $this->taskManager->getBy(['active' => true]);
    }
}
