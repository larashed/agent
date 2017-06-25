<?php

namespace Larashed\Agent;

use Larashed\Api\LarashedApi;
use Illuminate\Support\ServiceProvider;
use Larashed\Agent\Storage\StorageFactory;
use Larashed\Agent\Storage\AgentStorageInterface;
use Larashed\Agent\Commands\LarashedSendCommand;
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
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
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
            return new LarashedApi(config('larashed-agent.application_id'), config('larashed-agent.application_key'));
        });

        $this->app->singleton(Collector::class, function () {
            return new Collector();
        });

        $this->app->singleton(Agent::class, function ($app) {
            return new Agent($this->getStorageDriver(), $app[Collector::class]);
        });

        $this->commands([
            LarashedSendCommand::class
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
