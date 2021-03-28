<?php

namespace Larashed\Agent\Events;

use Carbon\Carbon;

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
     */
    public function __construct($id, $connection, $queue)
    {
        $this->id = (string) $id;
        $this->queue = str_replace('queues:', '', $queue);
        $this->connection = $connection;
        $this->dispatchedAt = Carbon::now()->format('c');
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'dispatched_at' => $this->dispatchedAt,
            'id'            => $this->id,
            'connection'    => $this->connection,
            'queue'         => $this->queue
        ];
    }
}
