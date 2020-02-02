<?php

namespace Larashed\Agent\Console\Commands;

use Illuminate\Console\Command;
use Larashed\Agent\Agent;
use Larashed\Agent\Api\LarashedApi;
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

        $storageDirectory = storage_path(
            rtrim(config('larashed.directory'), '/') . '/'
        );

        $executableDir = $storageDirectory . 'bin/';
        $executablePath = $executableDir . 'agent';

        $socketPath = $storageDirectory . trim(config('larashed.transport.engines.socket.file'), '/');

        $this->downloadAgent($executableDir, $executablePath);
        $this->runAgent($socketPath, $executablePath);
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

    /**
     * @param $socketPath
     * @param $executablePath
     */
    protected function runAgent($socketPath, $executablePath)
    {
        $arguments = [
            $executablePath, 'daemon',
            '--socket=' . $socketPath,
            '--env=' . config('app.env'),
            '--app-id=' . config('larashed.application_id'),
            '--app-key=' . config('larashed.application_key'),
            '--api-url=' . config('larashed.url'),
        ];

        $process = new Process($arguments, null, null, null, null);

        $this->info('Running: ' . $process->getCommandLine());

        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->error($buffer);
            } else {
                $this->info($buffer);
            }
        });

        $this->info($process->getOutput());
        $this->error($process->getErrorOutput());
    }

    /**
     * @param $executableDir
     * @param $executablePath
     */
    protected function downloadAgent($executableDir, $executablePath)
    {
        // @TODO improve this to enable upgrades
        if (file_exists($executablePath)) {
            return;
        }

        $this->line("Agent binary not found. Downloading.");

        $url = 'https://github.com/larashed/agent-go/releases/latest/download/agent_linux_amd64';

        mkdir($executableDir, 0777, true);

        copy($url, $executablePath);
        $this->line("Downloaded.");
        $this->line("Setting permissions");
        chmod($executablePath, 777);
        $this->line("Done. Agent binary ready.");
    }
}
