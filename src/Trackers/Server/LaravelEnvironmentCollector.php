<?php

namespace Larashed\Agent\Trackers\Server;
use Illuminate\Foundation\Application;


/**
 * Class EnvironmentCollector
 *
 * @package Larashed\Agent\Trackers\Server
 */
class LaravelEnvironmentCollector
{
    public function __construct(Application $application)
    {
        $this->app = $application;
    }

    public function appName()
    {
        return config('app.name');
    }

    /**
     * @return string
     */
    public function environment()
    {
        return config('app.env');
    }

    /**
     * @return string
     */
    public function url()
    {
        return config('app.url');
    }

    /**
     * @return string
     */
    public function laravelVersion()
    {
        return $this->app->version();
    }

    /**
     * @return string
     */
    public function queueDriver()
    {
        return config('queue.default');
    }

    /**
     * @return string
     */
    public function databaseDriver()
    {
        return config('database.default');
    }

    /**
     * @return string
     */
    public function cacheDriver()
    {
        return config('cache.default');
    }

    /**
     * @return string
     */
    public function mailDriver()
    {
        return config('mail.driver');
    }
}
