<?php declare (strict_types = 1);

namespace AppBundle\Service;

use ElephantIO\Client as Elephant;
use ElephantIO\Engine\SocketIO\Version2X;

class ProcessService
{
    /**
     * @var  Elephant
     */
    private $elephant;

    /**
     * Constructor
     *
     * @param string $appDomain
     * @param string $port
     */
    public function __construct($appDomain, $port)
    {
        $this->elephant = new Elephant(new Version2X(sprintf('https://%s:%s', $appDomain, $port)));
    }

    /**
     * Emit event message
     *
     * @param string $event
     * @param string $message
     */
    public function emit($event = null, $message = null)
    {
        if ($event == null || $message == null) {
            return;
        }
        $this->elephant->initialize();
        $this->elephant->emit('broadcast', [
            'event' => $event,
            'message' => $message
        ]);
        $this->elephant->close();
    }
}
