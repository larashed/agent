<?php

namespace Larashed\Agent\Console;

use Illuminate\Queue\Worker as QueueWorker;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Str;
use Larashed\Agent\Events\WorkerStarting;

/**
 * Class Worker
 *
 * @package Larashed\Agent\Console
 */
class Worker extends QueueWorker
{
    /**
     * @var string
     */
    public static $workerId;

    /**
     * @param string        $connectionName
     * @param string        $queue
     * @param WorkerOptions $options
     *
     * @return int
     */
    public function daemon($connectionName, $queue, WorkerOptions $options)
    {
        static::$workerId = $this->getRandomId();

        $this->events->dispatch(new WorkerStarting($connectionName, $queue, $options));

        return parent::daemon($connectionName, $queue, $options);
    }

    /**
     * @return string
     */
    protected function getRandomId()
    {
        return Str::random(32);
    }
}
