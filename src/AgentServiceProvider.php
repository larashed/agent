<?php

namespace Larashed\Agent;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Support\ServiceProvider;
use Larashed\Agent\Api\Config;
use Larashed\Agent\Api\LarashedApi;
use Larashed\Agent\Console\Commands\AgentCommand;
use Larashed\Agent\Console\Commands\DeployCommand;
use Larashed\Agent\Http\Middlewares\RequestTrackerMiddleware;
use Larashed\Agent\System\Measurements;
use Larashed\Agent\Trackers\Database\QueryExcluder;
use Larashed\Agent\Trackers\Database\QueryExcluderConfig;
use Larashed\Agent\Trackers\DatabaseQueryTracker;
use Larashed\Agent\Trackers\HttpRequestTracker;
use Larashed\Agent\Trackers\QueueJobTracker;
use Larashed\Agent\Trackers\WebhookRequestTracker;
use Larashed\Agent\Transport\DomainSocketTransport;
use Larashed\Agent\Transport\TransportInterface;

class AgentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if (Agent::isEnabled()) {
            $this->app[Agent::class]->start();
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->loadConfig();

        $this->commands([
            DeployCommand::class,
            AgentCommand::class,
        ]);

        if (!Agent::isEnabled()) {
            return;
        }

        $this->app->singleton(Measurements::class);
        $this->app->singleton(TransportInterface::class, $this->getTransportInstance());
        $this->app->singleton(LarashedApi::class, $this->getLarashedApiInstance());
        $this->app->singleton(RequestTrackerMiddleware::class);
        $this->app->singleton(Agent::class, $this->getAgentInstance());

        $this->loadMiddlewares();
        $this->loadRoutes();
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
    protected function getTransportInstance()
    {
        return function ($app) {
            $transport = config('larashed.transport.default');
            switch ($transport) {
                case 'socket':
                    $dir = config('larashed.directory');

                    return new DomainSocketTransport(
                        storage_path($dir . DIRECTORY_SEPARATOR . config('larashed.transport.engines.socket.file'))
                    );
            }

            throw new \InvalidArgumentException('Invalid Larashed configuration. Unknown transport: ' . $transport);
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
            $queryExcluder = new QueryExcluder(QueryExcluderConfig::fromConfig());

            $agent = new Agent($app[TransportInterface::class]);
            $agent->addTracker('queries', new DatabaseQueryTracker($app[Measurements::class], $queryExcluder));
            $agent->addTracker('job', new QueueJobTracker($agent, $app[Measurements::class]));
            $agent->addTracker('request', new HttpRequestTracker($app['events'], $app[Measurements::class]));
            $agent->addTracker('webhook', new WebhookRequestTracker($app['events'], $app[Measurements::class]));

            return $agent;
        };
    }
}
