<?php

namespace Larashed\Agent;

use Larashed\Agent\Trackers\TrackerInterface;
use Larashed\Agent\Transport\TransportInterface;

/**
 * Class Agent
 *
 * @package Larashed\Agent
 */
class Agent
{
    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @var TrackerInterface[]
     */
    protected $trackers;

    /**
     * Agent constructor.
     *
     * @param TransportInterface $storage
     */
    public function __construct(TransportInterface $storage)
    {
        $this->transport = $storage;
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
        $data = $this->gatherCollectedData();

        if (is_null($data)) {
            return;
        }

        $data['config'] = $this->environmentConfig();

        $this->transport->push($data);
    }

    public static function isEnabled()
    {
        $envs = collect(explode(",", config('larashed.ignored_environments')))
            ->map(function ($env) {
                return trim($env);
            })
            ->reject(function ($env) {
                return empty(trim($env));
            })->toArray();

        return !in_array(config('app.env'), $envs);
    }

    protected function gatherCollectedData()
    {
        $data = [];

        collect($this->trackers)->each(function (TrackerInterface $tracker, $name) use (&$data) {
            $data[$name] = $tracker->gather();
            $tracker->cleanup();
        });

        // remove empty keys
        foreach ($data as $key => $value) {
            if (empty($value)) {
                unset($data[$key]);
            }
        }

        if (empty($data)) {
            return null;
        }

        return $data;
    }

    protected function environmentConfig()
    {
        return [
            'name'            => config('app.name'),
            'env'             => config('app.env'),
            'url'             => config('app.url'),
            'drivers'         => [
                'queue'    => config('queue.default'),
                'database' => config('database.default'),
                'cache'    => config('cache.default'),
                'mail'     => config('mail.driver'),
            ],
            'laravel_version' => app()->version(),
            'hostname'        => gethostname(),
            'php_version'     => phpversion(),
        ];
    }
}
