<?php

namespace Larashed\Agent\Transport;

/**
 * Class DomainSocketTransport
 *
 * @package Larashed\Agent\Transport
 */
class DomainSocketTransport implements TransportInterface
{
    protected $path;

    public function __construct($socketPath)
    {
        $this->path = $socketPath;
    }

    public function push(array $record)
    {
        try {
            if (file_exists($this->path)) {
                $data = json_encode($record);
                $sock = stream_socket_client('unix://' . $this->path, $errorNumber, $errorMessage);
                fwrite($sock, $data);
            }
        } catch (\Exception $e) {
            // ignore error
        }
    }

    public function records($limit)
    {
        // TODO: Implement records() method.
    }

    public function remove(array $identifiers)
    {
        // TODO: Implement remove() method.
    }
}
