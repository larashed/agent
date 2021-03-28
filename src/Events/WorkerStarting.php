<?php

namespace Larashed\Agent\Events;

use Illuminate\Queue\WorkerOptions;

class WorkerStarting
{
    /**
     * @var string
     */
    public $connection;

    /**
     * @var string
     */
    public $queue;

    /**
     * @var WorkerOptions
     */
    public $options;

    public function __construct($connection, $queue, WorkerOptions $options)
    {
        $this->connection = $connection;
        $this->queue = $queue;
        $this->options = $options;
    }
}
