<?php

namespace Larashed\Agent\Queue\Queues;

use Illuminate\Queue\RedisQueue as BaseQueue;
use Larashed\Agent\Events\JobDispatched;

class RedisQueue extends BaseQueue
{
    use DispatchEventTrait;

    /**
     * Push a raw payload onto the queue.
     *
     * @param string $payload
     * @param string $queue
     * @param array  $options
     *
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $id = parent::pushRaw($payload, $queue, $options);

        $this->dispatchEvent(
            new JobDispatched($id, $this->getConnectionName(), $this->getQueue($queue))
        );

        return $id;
    }
}
