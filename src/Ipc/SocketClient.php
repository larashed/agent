<?php

namespace Larashed\Agent\Ipc;

class SocketClient
{
    const QUIT = 'quit';

    /**
     * @var string
     */
    protected $socketPath;

    /**
     * SocketClient constructor.
     *
     * @param string $socketPath
     */
    public function __construct($socketPath)
    {
        $this->socketPath = $socketPath;
    }

    /**
     * @param string $message
     */
    public function send($message)
    {
        try {
            if (file_exists($this->socketPath)) {
                $sock = stream_socket_client('unix://' . $this->socketPath, $errorNumber, $errorMessage);
                fwrite($sock, $message . "\n");
            }
        } catch (\Exception $e) {
            // ignore error
        }
    }
}
