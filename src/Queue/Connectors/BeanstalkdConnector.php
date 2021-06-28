<?php

namespace Larashed\Agent\Queue\Connectors;

use Illuminate\Queue\Connectors\BeanstalkdConnector as BaseConnector;
use Illuminate\Support\Arr;
use Larashed\Agent\Queue\Queues\BeanstalkdQueue;
use Pheanstalk\Connection;
use Pheanstalk\Pheanstalk;

class BeanstalkdConnector extends BaseConnector
{
    public function connect(array $config)
    {
        return new BeanstalkdQueue(
            $this->pheanstalk($config),
            $config['queue'],
            Arr::get($config, 'retry_after', Pheanstalk::DEFAULT_TTR),
            Arr::get($config, 'block_for', 0),
            Arr::get($config, 'after_commit', null)
        );
    }
}
