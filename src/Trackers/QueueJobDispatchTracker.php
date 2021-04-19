<?php

namespace Larashed\Agent\Trackers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherInterface;
use Larashed\Agent\Events\JobDispatched;
use Larashed\Agent\System\Measurements;
use Laravel\Horizon\Events\JobPushed;

class QueueJobDispatchTracker implements TrackerInterface
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
     * @var array
     */
    protected $dispatchedJobs = [];

    /**
     * QueueJobDispatchTracker constructor.
     *
     * @param DispatcherInterface $events
     * @param Measurements        $measurements
     */
    public function __construct(DispatcherInterface $events, Measurements $measurements)
    {
        $this->events = $events;
        $this->measurements = $measurements;
    }

    /**
     * @return void
     */
    public function bind()
    {
        if (class_exists('Laravel\Horizon\Events\JobPushed')) {
            $this->events->listen('Laravel\Horizon\Events\JobPushed', [$this, 'handleHorizonJobDispatchEvent']);
        }

        $this->events->listen(JobDispatched::class, [$this, 'handleJobDispatchEvent']);
    }

    /**
     * @return array
     */
    public function gather()
    {
        return $this->dispatchedJobs;
    }

    /**
     * @return void
     */
    public function cleanup()
    {
        $this->dispatchedJobs = [];
    }

    public function handleJobDispatchEvent(JobDispatched $event)
    {
        $this->dispatchedJobs[] = [
            'dispatched_at' => $event->dispatchedAt,
            'id'            => $event->id,
            'connection'    => $event->connection,
            'queue'         => $event->queue
        ];
    }

    /**
     * @param $event
     */
    public function handleHorizonJobDispatchEvent($event)
    {
        /** @var $event JobPushed */
        $this->dispatchedJobs[] = [
            'dispatched_at' => $this->measurements->microtime(),
            'id'            => $event->payload->id(),
            'connection'    => $event->connectionName,
            'queue'         => $event->queue
        ];
    }
}
