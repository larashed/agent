<?php

namespace Larashed\Agent\Trackers;

use Exception;
use Illuminate\Contracts\Events\Dispatcher as DispatcherInterface;
use Illuminate\Queue\Events\Looping;
use Illuminate\Queue\Events\WorkerStopping;
use Larashed\Agent\AgentConfig;
use Larashed\Agent\Api\LarashedApi;
use Larashed\Agent\Console\Worker;
use Larashed\Agent\Events\WorkerStarting;
use Larashed\Agent\System\Measurements;
use Illuminate\Support\Facades\Log;

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
    protected $loopCount = 0;

    /**
     * @var int
     */
    protected $pingInterval = 10;

    /**
     * QueueWorkerTracker constructor.
     *
     * @param DispatcherInterface $events
     * @param Measurements $measurements
     * @param LarashedApi $api
     * @param int $pingInterval
     */
    public function __construct(DispatcherInterface $events, Measurements $measurements, LarashedApi $api, $pingInterval = 10)
    {
        $this->events = $events;
        $this->measurements = $measurements;
        $this->api = $api;
        $this->pingInterval = $pingInterval;
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
        $data = [
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
        ];

        try {
            $this->api->sendQueueWorkerStartEvent($data);
        } catch (Exception $exception) {
            if (AgentConfig::debug()) {
                Log::error('larashed-agent.worker-start', [$exception->getMessage()]);
            }
        }
    }

    public function handleWorkerStopEvent(WorkerStopping $event)
    {
        try {
            $this->api->sendQueueWorkerStopEvent([
                'worker_id'  => Worker::$workerId,
                'exit_code'  => $event->status,
                'stopped_at' => $this->measurements->time(),
            ]);
        } catch (Exception $exception) {
            if (AgentConfig::debug()) {
                Log::error('larashed-agent.worker-stop', [$exception->getMessage()]);
            }
        }
    }

    public function handleWorkerLoopEvent(Looping $event)
    {
        $this->loopCount++;

        if ($this->loopCount % $this->pingInterval !== 0) {
            return;
        }

        try {
            $this->api->sendQueueWorkerPing([
                'worker_id' => Worker::$workerId,
                'pid'       => getmypid(),
            ]);
        } catch (Exception $exception) {
            if (AgentConfig::debug()) {
                Log::error('larashed-agent.worker-ping', [$exception->getMessage()]);
            }
        }
    }
}
