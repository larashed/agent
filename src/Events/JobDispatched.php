<?php

namespace Larashed\Agent\Events;

use Carbon\Carbon;
use Larashed\Agent\System\Measurements;

class JobDispatched
{
    public $id;
    public $queue;
    public $connection;
    public $dispatchedAt;

    /**
     * JobDispatched constructor.
     *
     * @param          $id
     * @param          $connection
     * @param          $queue
     * @param Carbon   $timestamp
     * @param int      $delay
     */
    public function __construct($id, $connection, $queue, Carbon $timestamp, $delay = 0)
    {
        $this->id = (string) $id;
        $this->queue = str_replace('queues:', '', $queue);
        $this->connection = $connection;

        /** @var Measurements $measurements */
        $measurements = app(Measurements::class);
        $this->dispatchedAt = $measurements->datetimeWithDelay($timestamp, $delay);
    }
}
