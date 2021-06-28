<?php

namespace Larashed\Agent\Queue\Queues;

use Carbon\Carbon;
use Illuminate\Queue\RedisQueue as BaseQueue;
use Larashed\Agent\Events\JobDispatched;

class RedisQueue extends BaseQueue
{
    use DispatchesEvent;

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
        $now = Carbon::now();

        $id = parent::pushRaw($payload, $queue, $options);

        $this->dispatchEvent(
            new JobDispatched($id, $this->getConnectionName(), $this->getQueue($queue), $now)
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
        $now = Carbon::now();

        $id = parent::laterRaw($delay, $payload, $queue);

        $this->dispatchEvent(new JobDispatched(
            $id,
            $this->getConnectionName(),
            $this->getQueue($queue),
            $now,
            $this->availableAt($delay)
        ));

        return $id;
    }
}
