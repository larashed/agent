<?php

namespace Larashed\Agent;

use App\Console\Commands\DeployCommand;
use Larashed\Api\LarashedApi;
use Illuminate\Support\ServiceProvider;
use Larashed\Agent\Storage\StorageFactory;
use Larashed\Agent\Storage\AgentStorageInterface;
use Larashed\Agent\Commands\DaemonCommand;
use Larashed\Agent\Http\Middlewares\TrackRequests;

class LarashedAgentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app[Agent::class]->boot();

        if (version_compare($this->app->version(), '5.3.0', 'lt')) {
            if (!$this->app->routesAreCached()) {
                require __DIR__ . '/routes.php';
            }
        } else {
            $this->loadRoutesFrom(__DIR__ . '/routes.php');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../install-stubs/config/larashed-agent.php', 'larashed-agent');

        $this->app->singleton(LarashedApi::class, function () {
            return new LarashedApi(
                config('larashed-agent.application_id'),
                config('larashed-agent.application_key'),
                config('larashed-agent.url'),
                config('larashed-agent.verify-ssl')
            );
        });

        $this->app->singleton(Collector::class, function () {
            return new Collector();
        });

        $this->app->singleton(Agent::class, function ($app) {
            return new Agent($this->getStorageDriver(), $app[Collector::class]);
        });

        $this->commands([
            DaemonCommand::class,
            DeployCommand::class
        ]);

        $this->publish();
        $this->pushTrackingMiddleware();
    }

    /**
     * @return AgentStorageInterface
     */
    protected function getStorageDriver()
    {
        return StorageFactory::buildFromConfig();
    }

    protected function publish()
    {
        $this->publishes(
            [__DIR__ . '/../install-stubs/database/migrations' => database_path('migrations')],
            'larashed.agent.migrations'
        );

        $this->publishes(
            [__DIR__ . '/../install-stubs/config/' => config_path()],
            'larashed.agent.config'
        );
    }

    protected function pushTrackingMiddleware()
    {
        /** @var \App\Http\Kernel $kernel */
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $kernel->pushMiddleware(TrackRequests::class);
    }
}
