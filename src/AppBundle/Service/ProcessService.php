<?php declare (strict_types = 1);

namespace AppBundle\Service;

use ElephantIO\Client as Elephant;
use ElephantIO\Engine\SocketIO\Version2X;
use ElephantIO\Exception\ServerConnectionFailureException;
use AppBundle\Document\User;

class ProcessService
{
    /**
     * @var  Elephant
     */
    private $elephant;

    /**
     * Constructor
     *
     * @param string       $appDomain
     * @param string       $port
     */
    public function __construct($appDomain, $port)
    {
        try {
            // Do not verify peer
            $options = [
                "context" => [
                    "ssl" => [
                        "verify_peer" => false,
                        "verify_peer_name" => false,
                    ],
                ],
            ];
            $server = new Version2X(sprintf('https://%s:%s', $appDomain, $port), $options);
            $this->elephant = new Elephant($server);
        } catch (ServerConnectionFailureException $e) {
            $this->elephant = null;
        }
    }

    /**
     * Emit event message
     *
     * @param User   $user
     * @param string $event
     * @param string $message
     */
    public function emit(User $user, $event = null, $message = null)
    {
        if ($this->elephant == null || $event == null || $message == null) {
            return;
        }

        try {
            $this->elephant->initialize();
            $this->elephant->emit('broadcast', [
                'event' => $event,
                'message' => $message,
                'user' => $user != null ? $user->getId() : null
            ]);
            $this->elephant->close();
        } catch (ServerConnectionFailureException $e) {
            return;
        }
    }
}
