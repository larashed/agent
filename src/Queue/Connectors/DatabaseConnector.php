<?php

namespace Larashed\Agent\Queue\Connectors;

use Illuminate\Queue\Connectors\DatabaseConnector as BaseConnector;
use Illuminate\Support\Arr;
use Larashed\Agent\Queue\Queues\DatabaseQueue;

class DatabaseConnector extends BaseConnector
{
    /**
     * @param array $config
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new DatabaseQueue(
            $this->connections->connection(Arr::get($config, 'connection')),
            Arr::get($config, 'table'),
            Arr::get($config, 'queue'),
            Arr::get($config, 'retry_after', 60),
            Arr::get($config, 'after_commit')
        );
    }
}
