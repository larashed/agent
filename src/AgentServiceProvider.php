<?php

namespace Larashed\Agent;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Larashed\Agent\Api\Config;
use Larashed\Agent\Api\LarashedApi;
use Larashed\Agent\Console\Commands\CronCommand;
use Larashed\Agent\Console\DaemonRestartHandler;
use Larashed\Agent\Console\Mutex;
use Larashed\Agent\Storage\StorageInterface;
use Larashed\Agent\Storage\FileStorage;
use Larashed\Agent\System\System;
use Larashed\Agent\Trackers\DatabaseQueryTracker;
use Larashed\Agent\Trackers\HttpRequestTracker;
use Larashed\Agent\Trackers\QueueJobTracker;
use Larashed\Agent\Trackers\WebhookRequestTracker;
use Larashed\Agent\Trackers\Database\QueryExcluder;
use Larashed\Agent\Trackers\Database\QueryExcluderConfig;
use Larashed\Agent\System\Measurements;
use Larashed\Agent\Console\Commands\ServerCommand;
use Larashed\Agent\Console\Commands\DeployCommand;
use Larashed\Agent\Console\Commands\DaemonCommand;
use Larashed\Agent\Http\Middlewares\RequestTrackerMiddleware;

class AgentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app[Agent::class]->start();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(StorageInterface::class, $this->getStorageInstance());
        $this->app->singleton(LarashedApi::class, $this->getLarashedApiInstance());
        $this->app->singleton(Agent::class, $this->getAgentInstance());
        $this->app->singleton(RequestTrackerMiddleware::class);
        $this->app->singleton(DaemonRestartHandler::class, function ($app) {
            return new DaemonRestartHandler($app[Filesystem::class], config('larashed.restart-file'));
        });
        $this->app->singleton(Mutex::class, function ($app) {
            return new Mutex($app[Filesystem::class], config('larashed.mutex-file'));
        });

        $this->commands([
            DaemonCommand::class,
            DeployCommand::class,
            ServerCommand::class,
            CronCommand::class,
        ]);

        $this->loadMiddlewares();
        $this->loadRoutes();
        $this->loadConfig();
    }

    /**
     * Load Larashed middlewares
     */
    protected function loadMiddlewares()
    {
        /** @var \App\Http\Kernel $kernel */
        $kernel = $this->app->make(HttpKernel::class);
        $kernel->pushMiddleware(RequestTrackerMiddleware::class);
    }

    /**
     * Load Larashed routes
     */
    protected function loadRoutes()
    {
        if (!$this->app->routesAreCached()) {
            require __DIR__ . '/routes.php';

            return;
        }
    }

    /**
     * Load Larashed config
     */
    protected function loadConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/../install-stubs/config/larashed.php', 'larashed');
        $this->publishes([
            __DIR__ . '/../install-stubs/config/' => config_path()
        ], 'larashed');
    }

    /**
     * Builds Larashed API client
     *
     * @return \Closure
     */
    protected function getLarashedApiInstance()
    {
        return function () {
            return new LarashedApi(
                new Config(
                    config('larashed.application_id'),
                    config('larashed.application_key'),
                    config('app.env'),
                    config('larashed.url'),
                    config('larashed.verify-ssl')
                )
            );
        };
    }

    /**
     * @return \Closure
     */
    protected function getStorageInstance()
    {
        return function ($app) {
            $storage = new FileStorage(
                $app['filesystem'],
                config('larashed.storage.engines.file.disk'),
                config('larashed.storage.engines.file.directory')
            );

            return $storage;
        };
    }

    /**
     * Builds Agent instance
     *
     * @return \Closure
     */
    protected function getAgentInstance()
    {
        return function ($app) {
            $measurements = new Measurements();
            $queryExcluder = new QueryExcluder(QueryExcluderConfig::fromConfig());

            $agent = new Agent($app[StorageInterface::class]);
            $agent->addTracker('queries', new DatabaseQueryTracker($measurements, $queryExcluder));
            $agent->addTracker('job', new QueueJobTracker($agent, $measurements));
            $agent->addTracker('request', new HttpRequestTracker($app['events'], $measurements));
            $agent->addTracker('webhook', new WebhookRequestTracker($app['events'], $measurements));

            return $agent;
        };
    }
}
