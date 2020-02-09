<?php

namespace Larashed\Agent\Transport;

use Larashed\Agent\Ipc\SocketClient;

/**
 * Class DomainSocketTransport
 *
 * @package Larashed\Agent\Transport
 */
class DomainSocketTransport implements TransportInterface
{
    /**
     * @var SocketClient
     */
    protected $client;

    /**
     * DomainSocketTransport constructor.
     *
     * @param SocketClient $client
     */
    public function __construct(SocketClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param array $record
     *
     * @return mixed|void
     */
    public function push(array $record)
    {
       $this->client->send((string) json_encode($record));
    }
}
