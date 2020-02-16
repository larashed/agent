<?php

namespace Larashed\Agent\Console\Commands;

use Illuminate\Console\Command;
use Larashed\Agent\Agent;
use Larashed\Agent\AgentConfig;
use Larashed\Agent\Console\GoAgent;

/**
 * Class AgentQuitCommand
 *
 * @package Larashed\Agent\Console\Commands
 */
class AgentQuitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larashed:agent-quit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a quit signal to the agent';

    public function handle()
    {
        if (!Agent::isEnabled()) {
            $this->error('Larashed agent is disabled for this environment');
            exit;
        }

        $agent = new GoAgent(app(AgentConfig::class));
        if ($agent->isInstalled()) {
            $agent->signalQuit();
        }
    }
}
