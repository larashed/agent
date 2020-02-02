<?php

namespace Larashed\Agent\Trackers;

use Illuminate\Contracts\Foundation\Application;
use Larashed\Agent\System\Measurements;
use Larashed\Agent\Trackers\Server\LaravelEnvironmentCollector;
use RuntimeException;

/**
 * Class ApplicationEnvironmentTracker
 *
 * @package Larashed\Agent\Trackers
 */
class ApplicationEnvironmentTracker implements TrackerInterface
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Measurements
     */
    protected $measurements;

    /**
     * @var LaravelEnvironmentCollector
     */
    protected $laravelCollector;

    /**
     * EnvironmentTracker constructor.
     *
     * @param Application                 $app
     * @param Measurements                $measurements
     * @param LaravelEnvironmentCollector $laravelCollector
     */
    public function __construct(
        Application $app,
        Measurements $measurements,
        LaravelEnvironmentCollector $laravelCollector
    ) {
        $this->app = $app;
        $this->measurements = $measurements;
        $this->laravelCollector = $laravelCollector;
    }

    public function bind()
    {
        if (!$this->app->runningInConsole()) {
            throw new RuntimeException('Server monitoring cannot be enabled in a web environment');
        }
    }

    /**
     * Gather server environment data
     *
     * @return array
     */
    public function gather()
    {
        $data = [
            'created_at' => $this->measurements->time(),
            'app'        => [
                'name'            => $this->laravelCollector->appName(),
                'url'             => $this->laravelCollector->url(),
                'drivers'         => [
                    'queue'    => $this->laravelCollector->queueDriver(),
                    'database' => $this->laravelCollector->databaseDriver(),
                    'cache'    => $this->laravelCollector->cacheDriver(),
                    'mail'     => $this->laravelCollector->mailDriver(),
                ],
                'laravel_version' => $this->laravelCollector->laravelVersion(),
            ]
        ];

        return $data;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function cleanup()
    {
    }
}
