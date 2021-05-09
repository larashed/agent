<?php

namespace Larashed\Agent\Trackers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherInterface;
use Illuminate\Queue\Events\Looping;
use Illuminate\Queue\Events\WorkerStopping;
use Larashed\Agent\Api\LarashedApi;
use Larashed\Agent\Console\Worker;
use Larashed\Agent\Events\WorkerStarting;
use Larashed\Agent\System\Measurements;

class QueueWorkerTracker implements TrackerInterface
{
    /**
     * @var DispatcherInterface
     */
    protected $events;

    /**
     * @var Measurements
     */
    protected $measurements;

    /**
     * @var LarashedApi
     */
    protected $api;

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * HttpRequestTracker constructor.
     *
     * @param DispatcherInterface $events
     * @param Measurements        $measurements
     * @param LarashedApi         $api
     */
    public function __construct(DispatcherInterface $events, Measurements $measurements, LarashedApi $api)
    {
        $this->events = $events;
        $this->measurements = $measurements;
        $this->api = $api;
    }

    public function bind()
    {
        $this->events->listen(WorkerStarting::class, [$this, 'handleWorkerStartEvent']);
        $this->events->listen(WorkerStopping::class, [$this, 'handleWorkerStopEvent']);
        $this->events->listen(Looping::class, [$this, 'handleWorkerLoopEvent']);
    }

    public function gather()
    {
        return [];
    }

    public function cleanup()
    {
    }

    public function handleWorkerStartEvent(WorkerStarting $event)
    {
        $this->api->sendQueueWorkerStartEvent([
            'connection'   => $event->connection,
            'queue'        => $event->queue,
            'pid'          => getmypid(),
            'worker_id'    => Worker::$workerId,
            'memory_usage' => memory_get_usage(true),
            'options'      => [
                'name'                => $event->options->name,
                'max_time'            => $event->options->maxTime,
                'max_jobs'            => $event->options->maxJobs,
                'max_tries'           => $event->options->maxTries,
                'memory_limit'        => $event->options->memory,
                'time_limit'          => $event->options->timeout,
                'stop_when_empty'     => $event->options->stopWhenEmpty,
                'retry_after_seconds' => $event->options->backoff,
                'sleep'               => $event->options->sleep,
                'run_in_maintenance'  => $event->options->force,
            ]
        ]);
    }

    public function handleWorkerStopEvent(WorkerStopping $event)
    {
        $this->api->sendQueueWorkerStopEvent([
            'worker_id'  => Worker::$workerId,
            'exit_code'  => $event->status,
            'stopped_at' => $this->measurements->time(),
        ]);
    }

    public function handleWorkerLoopEvent(Looping $event)
    {
        if ($this->count % 10 == 0) {
            $this->api->sendQueueWorkerPing([
                'worker_id' => Worker::$workerId,
                'pid'       => getmypid(),
            ]);
        }

        $this->count++;
    }
}
