<?php

namespace Larashed\Agent\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Larashed\Agent\Agent;
use Larashed\Agent\AgentConfig;
use Larashed\Agent\Api\LarashedApi;
use Larashed\Agent\Console\GoAgent;
use Larashed\Agent\Ipc\SocketClient;

/**
 * Class AgentCommand
 *
 * @package Larashed\Agent\Console\Commands
 */
class AgentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larashed:agent
        {--no-update}
        {--socket-type= : Socket type (unix or tcp)}
        {--socket-address= : Socket address (path or TCP address)}
        {--log-level=info}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the Larashed agent';

    /**
     * @var LarashedApi
     */
    protected $api;

    /**
     * Starts the daemon and sends collected data
     */
    public function handle()
    {
        $this->performChecks();

        $this->api = app(LarashedApi::class);

        $this->runAgent();
    }

    protected function runAgent()
    {
        /** @var GoAgent $agent */
        $agent = app(GoAgent::class);
        $logLevel = $this->option('log-level');
        $socketType = $this->option('socket-type');
        $socketAddress = $this->option('socket-address');

        if ($this->option('no-update')) {
            $agent->run($socketType, $socketAddress, $logLevel);

            return;
        }

        if (!$agent->isInstalled()) {
            $agent->install();
        } else {
            if ($agent->hasUpdate()) {
                $agent->update();
            }
        }

        $agent->run($socketType, $socketAddress, $logLevel);
    }

    protected function environmentIsSupported()
    {
        $os = strtolower(PHP_OS);

        return Str::contains($os, ['linux', 'darwin']);
    }

    protected function performChecks()
    {
        if (!Agent::isEnabled()) {
            $this->error('Larashed agent is disabled for this environment.');
            exit;
        }

        if (!$this->environmentIsSupported()) {
            $this->error(ucfirst(PHP_OS) . ' OS is not supported. Please contact us.');
            exit;
        }

        /** @var SocketClient $client */
        $client = app(SocketClient::class);

        if (empty(config('app.env'))) {
            $this->error('APP_ENV environment variable is missing a value.');
            exit;
        }

        if (empty($client->getSocketType())) {
            $this->error('LARASHED_TRANSPORT environment variable is missing a value.');
            exit;
        }

        if ($client->usesUnixSocket() && empty($client->getSocketAddress())) {
            $this->error('LARASHED_SOCKET_DIR environment variable is missing a value.');
            exit;
        }

        if ($client->usesTcpSocket() && empty($client->getSocketAddress())) {
            $this->error('LARASHED_TCP_ADDRESS environment variable is missing a value.');
            exit;
        }

        /** @var AgentConfig $config */
        $config = app(AgentConfig::class);

        if (empty($config->getApplicationId()) || empty($config->getApplicationKey())) {
            $this->error('LARASHED_APP_ID and LARASHED_APP_KEY environment variables are missing values.');
            exit;
        }
    }
}
