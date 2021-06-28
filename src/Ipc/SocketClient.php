<?php

namespace Larashed\Agent\Ipc;

use Exception;
use Illuminate\Support\Facades\Log;
use Larashed\Agent\AgentConfig;

class SocketClient
{
    const QUIT = 'quit';

    const UNIX = 'unix';
    const TCP  = 'tcp';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $address;

    /**
     * SocketClient constructor.
     *
     * @param string $type
     * @param string $address
     */
    public function __construct($type, $address)
    {
        $this->type = $type;
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getSocketType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function usesUnixSocket()
    {
        return $this->type === self::UNIX;
    }

    /**
     * @return bool
     */
    public function usesTcpSocket()
    {
        return $this->type === self::TCP;
    }

    /**
     * @return string
     */
    public function getSocketAddress()
    {
        return $this->address;
    }

    /**
     * @param string $message
     */
    public function send($message)
    {
        if ($this->usesUnixSocket() && !file_exists($this->address)) {
            return;
        }

        $timeout = 1.0;

        try {
            $sock = stream_socket_client($this->type . '://' . $this->address, $errorNumber, $errorMessage, $timeout);
            fwrite($sock, $message);
        } catch (Exception $exception) {
            if (AgentConfig::debug()) {
                Log::error('larashed-agent.ipc', [$exception->getMessage()]);
            }
        }
    }
}
