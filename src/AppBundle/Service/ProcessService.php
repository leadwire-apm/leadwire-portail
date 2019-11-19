<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Process;
use AppBundle\Document\User;
use AppBundle\Manager\ProcessManager;
use JMS\Serializer\SerializerInterface;

class ProcessService
{
    /**
     * @var ProcessManager
     */
    private $processManager;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(ProcessManager $processManager, SerializerInterface $serializer)
    {
        $this->processManager = $processManager;
        $this->serializer = $serializer;
    }

    /**
     * Create process
     *
     * @param User   $user
     * @param string $message
     *
     * @return Process
     */
    public function createProcess(User $user, $message)
    {
        $process = $this->processManager->create($user, $message);

        return $process;
    }

    /**
     * Create login process
     *
     * @param string $message
     *
     * @return Process
     */
    public function createLoginProcess($message)
    {
        $process = $this->processManager->createLogin($message);

        return $process;
    }

    /**
     * Update process
     *
     * @param Process $process
     * @param string  $status
     *
     * @return Process
     */
    public function updateInProgressProcess(Process $process, $message = "")
    {
        $process->setStatus(Process::STATUS_IN_PROGRESS);
        $process->setMessage($message);

        $this->processManager->update($process);

        return $process;
    }

    /**
     * Update process
     *
     * @param Process $process
     * @param string  $status
     *
     * @return Process
     */
    public function updateInProgressLoginProcess(Process $process, $message = "")
    {
        $process->setStatus(Process::STATUS_IN_PROGRESS);
        $process->setMessage($message);
        $process->setIsNewLogin(false);

        $this->processManager->update($process);

        return $process;
    }

    /**
     * sucess process
     *
     * @param Process $process
     * @param string  $status
     *
     * @return Process
     */
    public function successProcess(Process $process)
    {
        $process->setStatus(Process::STATUS_SUCCEEDED);
        $this->processManager->update($process);

        return $process;
    }

    /**
     * fail process
     *
     * @param Process $process
     * @param string  $status
     *
     * @return Process
     */
    public function failProcess(Process $process)
    {
        $process->setStatus(Process::STATUS_FAILED);
        $this->processManager->update($process);

        return $process;
    }

    /**
     * sucess process
     *
     * @param Process $process
     * @param string  $status
     *
     * @return Process
     */
    public function successLoginProcess(Process $process)
    {
        $process->setStatus(Process::STATUS_SUCCEEDED);
        $process->setMessage("Finalizing login");
        $process->setIsNewLogin(false);
        $this->processManager->update($process);

        return $process;
    }

    /**
     * fail process
     *
     * @param Process $process
     * @param string  $status
     *
     * @return Process
     */
    public function failLoginProcess(Process $process)
    {
        $process->setStatus(Process::STATUS_FAILED);
        $process->setIsNewLogin(false);
        $this->processManager->update($process);

        return $process;
    }

    /**
     * Get in progress process by user
     *
     * @param User $user
     *
     * @return Process
     */
    public function getInProgressProcessByUser(User $user = null)
    {
        if ($user == null) {
            return null;
        }

        $params = [
            'status' => Process::STATUS_IN_PROGRESS,
            'user' => $user
        ];
        $process = $this->processManager->getOneBy($params);

        return $process;
    }

    /**
     * Get in progress process by user
     *
     * @param bool $isNewLogin
     *
     * @return Process
     */
    public function getInProgressLoginProcess($isNewLogin)
    {
        $params = [
            'status' => Process::STATUS_IN_PROGRESS,
            'isNewLogin' => $isNewLogin
        ];
        $process = $this->processManager->getOneBy($params);

        return $process;
    }

    /**
     * Get in progress process by user
     *
     * @param bool $isNewLogin
     *
     * @return Process
     */
    public function getLoginProcess()
    {
        $params = [
            'status' => Process::STATUS_IN_PROGRESS
        ];
        $process = $this->processManager->getOneBy($params);

        return $process;
    }
}
