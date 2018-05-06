<?php

namespace Larashed\Agent;

use Larashed\Agent\Storage\StorageInterface;
use Larashed\Agent\Trackers\TrackerInterface;

/**
 * Class Agent
 *
 * @package Larashed\Agent
 */
class Agent
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var TrackerInterface[]
     */
    protected $trackers;

    /**
     * Agent constructor.
     *
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param string           $name
     * @param TrackerInterface $tracker
     *
     * @return $this
     */
    public function addTracker($name, TrackerInterface $tracker)
    {
        $this->trackers[$name] = $tracker;

        return $this;
    }

    /**
     * Starts tracking
     */
    public function start()
    {
        collect($this->trackers)->each(function (TrackerInterface $tracker) {
            $tracker->bind();
        });
    }

    /**
     * Stops tracking and stores collected data
     */
    public function stop()
    {
        $data = [];

        collect($this->trackers)->each(function (TrackerInterface $tracker, $name) use (&$data) {
            $data[$name] = $tracker->gather();
            $tracker->cleanup();
        });

        $this->storage->push($data);
    }
}
