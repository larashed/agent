<?php

namespace Larashed\Agent\Queue\Connectors;

use Illuminate\Queue\Connectors\RedisConnector as BaseConnector;
use Illuminate\Support\Arr;
use Larashed\Agent\Queue\Queues\RedisQueue;

class RedisConnector extends BaseConnector
{
    /**
     * @param array $config
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new RedisQueue(
            $this->redis, $config['queue'],
            Arr::get($config, 'connection', $this->connection),
            Arr::get($config, 'retry_after', 60),
            Arr::get($config, 'block_for'),
            Arr::get($config, 'after_commit')
        );
    }
}
