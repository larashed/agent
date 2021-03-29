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

    /**
     * Push a raw job onto the queue after a delay.
     *
     * @param \DateTimeInterface|\DateInterval|int $delay
     * @param string                               $payload
     * @param string|null                          $queue
     *
     * @return mixed
     */
    protected function laterRaw($delay, $payload, $queue = null)
    {
        $id = parent::laterRaw($delay, $payload, $queue);

        $this->dispatchEvent(
            new JobDispatched($id, $this->getConnectionName(), $this->getQueue($queue), $this->availableAt($delay))
        );

        return $id;
    }
}
