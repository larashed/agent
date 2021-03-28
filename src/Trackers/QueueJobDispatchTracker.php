<?php

namespace Larashed\Agent\Trackers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherInterface;
use Larashed\Agent\Events\JobDispatched;
use Larashed\Agent\System\Measurements;

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
    protected $jobs = [];

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

    public function bind()
    {
        $this->events->listen(JobDispatched::class, [$this, 'onJobDispatchedEvent']);
    }

    public function gather()
    {
        return collect($this->jobs);
    }

    public function cleanup()
    {
        $this->jobs = [];
    }

    public function onJobDispatchedEvent(JobDispatched $event)
    {
        $this->jobs[] = $event->toArray();
    }
}
