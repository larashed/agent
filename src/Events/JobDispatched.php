<?php

namespace Larashed\Agent\Events;

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
     * @param $id
     * @param $connection
     * @param $queue
     * @param null $delay
     */
    public function __construct($id, $connection, $queue, $delay = null)
    {
        $this->id = (string) $id;
        $this->queue = str_replace('queues:', '', $queue);
        $this->connection = $connection;
        $this->dispatchedAt = app(Measurements::class)->time($delay);
    }
}
