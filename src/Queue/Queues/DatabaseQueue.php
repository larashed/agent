<?php

namespace Larashed\Agent\Queue\Queues;

use Carbon\Carbon;
use Illuminate\Queue\DatabaseQueue as BaseQueue;
use Larashed\Agent\Events\JobDispatched;

class DatabaseQueue extends BaseQueue
{
    use DispatchesEvent;

    /**
     * Push a raw payload to the database with a given delay.
     *
     * @param string|null                          $queue
     * @param string                               $payload
     * @param \DateTimeInterface|\DateInterval|int $delay
     * @param int                                  $attempts
     *
     * @return mixed
     */
    protected function pushToDatabase($queue, $payload, $delay = 0, $attempts = 0)
    {
        $now = Carbon::now();

        $id = $this->database->table($this->table)->insertGetId($this->buildDatabaseRecord(
            $this->getQueue($queue), $payload, $this->availableAt($delay), $attempts
        ));

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
