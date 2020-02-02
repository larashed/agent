<?php

namespace Larashed\Agent\Trackers;

use Closure;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Larashed\Agent\Agent;
use Larashed\Agent\System\Measurements;
use Larashed\Agent\Trackers\Queue\Job;

/**
 * Class QueueJobTracker
 *
 * @package Larashed\Agent\Trackers
 */
class QueueJobTracker implements TrackerInterface
{
    /**
     * @var Measurements
     */
    protected $measurements;

    /**
     * @var Agent
     */
    protected $agent;

    /**
     * @var Job
     */
    protected $job;

    /**
     * QueueJobTracker constructor.
     *
     * @param Agent        $agent
     * @param Measurements $measurements
     */
    public function __construct(Agent $agent, Measurements $measurements)
    {
        $this->agent = $agent;
        $this->measurements = $measurements;
    }

    /**
     * Bind queue events
     */
    public function bind()
    {
        Queue::before($this->onJobStartCallback());
        Queue::after($this->onJobEndCallback());
        Queue::exceptionOccurred($this->onJobFailureCallback());
    }

    /**
     * Gather tracker data
     *
     * @return array
     */
    public function gather()
    {
        $job = [];

        if (!is_null($this->job)) {
            $job = $this->job->toArray();
        }

        return $job;
    }

    /**
     * Cleanup job tracker data
     *
     * @return $this
     */
    public function cleanup()
    {
        $this->job = null;

        return $this;
    }

    /**
     * Collect job data before it starts
     *
     * @return Closure
     */
    protected function onJobStartCallback()
    {
        return function (JobProcessing $event) {
            $this->job = new Job($this->measurements, $event->job);
        };
    }

    /**
     * Collect job data once it's ended
     *
     * @return Closure
     */
    protected function onJobEndCallback()
    {
        return function (JobProcessed $event) {
            $this->job->finalize($event->connectionName);
            $this->agent->stop();
        };
    }

    /**
     * Collect failed job data
     *
     * @return Closure
     */
    protected function onJobFailureCallback()
    {
        return function (JobExceptionOccurred $event) {
            $this->job->finalize($event->connectionName, $event->exception);
            $this->agent->stop();
        };
    }
}
