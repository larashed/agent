<?php

namespace Larashed\Agent;

use Illuminate\Support\Facades\Route;
use Larashed\Agent\Storage\StorageInterface;
use Larashed\Agent\Trackers\ServerEnvironmentTracker;
use Larashed\Api\LarashedApi;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Larashed\Agent\Storage\FileStorage;
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
        if (!config('larashed.enabled')) {
            return;
        }

        $this->app[Agent::class]->start();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (!config('larashed.enabled')) {
            return;
        }

        $this->app->singleton(StorageInterface::class, $this->getStorageInstance());
        $this->app->singleton(LarashedApi::class, $this->getLarashedApiInstance());
        $this->app->singleton(Agent::class, $this->getAgentInstance());
        $this->app->singleton(RequestTrackerMiddleware::class);

        $this->commands([
            DaemonCommand::class,
            DeployCommand::class,
            ServerCommand::class
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
        ], 'larashed.config');
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
                config('larashed-agent.application_id'),
                config('larashed-agent.application_key'),
                config('larashed-agent.url'),
                config('larashed-agent.verify-ssl')
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
