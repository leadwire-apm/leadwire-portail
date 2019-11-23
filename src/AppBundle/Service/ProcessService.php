<?php declare (strict_types = 1);

namespace AppBundle\Service;

use ElephantIO\Client as Elephant;
use ElephantIO\Engine\SocketIO\Version1X;

class ProcessService
{
    /**
     * @var  Elephant
     */
    private $elephant;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->elephant = new Elephant(new Version1X('http://localhost:8000'));
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
