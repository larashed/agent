<?php

namespace Larashed\Agent;

use Larashed\Agent\Storage\AgentStorageInterface;
use Larashed\Agent\Trackers\JobTracker;
use Larashed\Agent\Trackers\QueryTracker;

/**
 * Class Agent
 *
 * @package Larashed\Agent
 */
class Agent
{
    /**
     * @var AgentStorageInterface
     */
    protected $storage;

    /**
     * @var Collector
     */
    protected $collector;

    /**
     * @var array
     */
    protected $trackers = [
        JobTracker::class,
        QueryTracker::class
    ];

    /**
     * Agent constructor.
     *
     * @param AgentStorageInterface $storage
     * @param Collector             $collector
     */
    public function __construct(AgentStorageInterface $storage, Collector $collector)
    {
        $this->storage = $storage;
        $this->collector = $collector;
    }

    /**
     * @return Collector
     */
    public function getCollector()
    {
        return $this->collector;
    }

    /**
     * @return AgentStorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     *  Bind application component trackers
     */
    public function boot()
    {
        foreach ($this->trackers as $tracker) {
            $jobs = new $tracker($this);
            $jobs->bind();
        }
    }

    /**
     * Set request and response data
     *
     * @param $request
     * @param $response
     */
    public function trackHttpData($request, $response)
    {
        $this->collector->setRequest($request)->setResponse($response);
    }

    /**
     * Store collected data using the selected storage engine
     */
    public function terminate()
    {
        $data = $this->collector->getData();

        $this->collector->clearRecords();

        $this->storage->addRecord($data);
    }
}
