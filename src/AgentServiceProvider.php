<?php

namespace Larashed\Agent;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Support\ServiceProvider;
use Larashed\Agent\Api\LarashedApi;
use Larashed\Agent\Console\Commands\AgentCommand;
use Larashed\Agent\Console\Commands\AgentQuitCommand;
use Larashed\Agent\Console\Commands\DeployCommand;
use Larashed\Agent\Console\GoAgent;
use Larashed\Agent\Http\Middlewares\RequestTrackerMiddleware;
use Larashed\Agent\Ipc\SocketClient;
use Larashed\Agent\System\Measurements;
use Larashed\Agent\Trackers\Database\QueryExcluder;
use Larashed\Agent\Trackers\Database\QueryExcluderConfig;
use Larashed\Agent\Trackers\DatabaseQueryTracker;
use Larashed\Agent\Trackers\HttpRequestTracker;
use Larashed\Agent\Trackers\LogTracker;
use Larashed\Agent\Trackers\QueueJobTracker;
use Larashed\Agent\Trackers\WebhookRequestTracker;
use Larashed\Agent\Transport\SocketTransport;
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

        $this->app->singleton(AgentConfig::class, $this->getAgentConfigInstance());

        $this->commands([
            DeployCommand::class,
            AgentCommand::class,
            AgentQuitCommand::class,
        ]);

        if (!Agent::isEnabled()) {
            return;
        }

        $this->app->singleton(Measurements::class);
        $this->app->singleton(SocketClient::class, $this->getSocketClientInstance());
        $this->app->singleton(TransportInterface::class, $this->getTransportInstance());
        $this->app->singleton(LarashedApi::class, $this->getLarashedApiInstance());
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
     * Buils GoAgent
     *
     * @return \Closure
     */
    protected function getGoAgentInstance()
    {
        return function ($app) {
            return new GoAgent(
                $app[AgentConfig::class], $app[SocketClient::class]
            );
        };
    }

    /**
     * Builds Larashed API client
     *
     * @return \Closure
     */
    protected function getLarashedApiInstance()
    {
        return function ($app) {
            return new LarashedApi(
                $app[AgentConfig::class]
            );
        };
    }

    /**
     * @return \Closure
     */
    protected function getTransportInstance()
    {
        return function ($app) {
            return new SocketTransport(
                $app[SocketClient::class]
            );
        };
    }

    protected function getSocketClientInstance()
    {
        return function ($app) {
            /** @var AgentConfig $config */
            $config = $app[AgentConfig::class];

            $transport = config('larashed.transport.default', SocketClient::UNIX);
            $address = config('larashed.transport.engines.' . $transport . '.address');

            if ($transport === SocketClient::UNIX) {
                if ($address === '.') {
                    $address = $config->getStorageDirectory() . AgentConfig::SOCKET_FILE;
                } else {
                    $address .= AgentConfig::SOCKET_FILE;
                }
            }

            return new SocketClient($transport, $address);
        };
    }

    protected function getAgentConfigInstance()
    {
        return function () {
            return new AgentConfig(
                config('larashed.application_id'),
                config('larashed.application_key'),
                config('app.env'),
                config('larashed.directory'),
                config('larashed.url'),
                config('larashed.verify-ssl')
            );
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
            if (config('larashed.logging_enabled')) {
                $agent->addTracker('logs', new LogTracker($app['events']));
            }

            $agent->addTracker('queries', new DatabaseQueryTracker($app[Measurements::class], $queryExcluder));
            $agent->addTracker('job', new QueueJobTracker($agent, $app[Measurements::class]));
            $agent->addTracker('request', new HttpRequestTracker($app['events'], $app[Measurements::class]));
            $agent->addTracker('webhook', new WebhookRequestTracker($app['events'], $app[Measurements::class]));

            return $agent;
        };
    }
}
