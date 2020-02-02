<?php

namespace Larashed\Agent\Console\Commands;

use Illuminate\Console\Command;
use Larashed\Agent\Agent;
use Larashed\Agent\AgentConfig;
use Larashed\Agent\Api\LarashedApi;
use Larashed\Agent\Console\GoAgent;
use Larashed\Agent\Trackers\ApplicationEnvironmentTracker;
use Larashed\Agent\Trackers\ServerInformationTracker;
use Symfony\Component\Process\Process;

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
    protected $signature = 'larashed:agent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the Larashed agent';

    /**
     * @var ApplicationEnvironmentTracker
     */
    protected $environmentTracker;

    /**
     * @var ServerInformationTracker
     */
    protected $serverTracker;

    /**
     * @var LarashedApi
     */
    protected $api;

    /**
     * Starts the daemon and sends collected data
     */
    public function handle()
    {
        if (!Agent::isEnabled()) {
            $this->error('Larashed agent is disabled for this environment');
            exit;
        }

        $this->environmentTracker = app(ApplicationEnvironmentTracker::class);
        $this->serverTracker = app(ServerInformationTracker::class);
        $this->api = app(LarashedApi::class);

        try {
            $this->updateEnvironmentInformation();
            $this->updateServerInformation();
        } catch (\Exception $exception) {
            $this->error("Agent start error: " . $exception->getMessage());
            exit;
        }

        $this->runAgent();
    }

    /**
     * @throws \Larashed\Agent\Api\LarashedApiException
     */
    protected function updateEnvironmentInformation()
    {
        $data = $this->environmentTracker->gather();
        $this->api->sendEnvironmentInformation($data);
    }

    /**
     * @throws \Larashed\Agent\Api\LarashedApiException
     */
    protected function updateServerInformation()
    {
        $data = $this->serverTracker->gather();
        $this->api->sendServerInformation($data);
    }

    protected function runAgent()
    {
        $agent = new GoAgent(app(AgentConfig::class), $this);
        $agent->installOrUpgrade();
        $agent->runDaemonCommand();
    }
}
