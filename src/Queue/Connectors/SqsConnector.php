<?php

namespace Larashed\Agent\Queue\Connectors;

use Aws\Sqs\SqsClient;
use Illuminate\Queue\Connectors\SqsConnector as BaseConnector;
use Illuminate\Support\Arr;
use Larashed\Agent\Queue\Queues\SqsQueue;

class SqsConnector extends BaseConnector
{
    public function connect(array $config)
    {
        $config = $this->getDefaultConfiguration($config);

        if (! empty($config['key']) && ! empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        return new SqsQueue(
            new SqsClient($config),
            $config['queue'],
            Arr::get($config, 'prefix', ''),
            Arr::get($config, 'suffix', ''),
            Arr::get($config, 'after_commit')
        );
    }
}
