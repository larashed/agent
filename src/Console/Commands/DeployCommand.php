<?php

namespace Larashed\Agent\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Larashed\Agent\Agent;
use Larashed\Agent\Api\LarashedApi;
use Larashed\Agent\Api\LarashedApiException;
use Larashed\Agent\Console\GoAgent;
use Larashed\Agent\System\Measurements;

/**
 * Class DeployCommand
 *
 * @package Larashed\Agent\Console\Commands
 */
class DeployCommand extends Command
{
    /**
     * @var LarashedApi
     */
    protected $api;

    /**
     * @var Measurements
     */
    protected $measurements;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larashed:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends the latest git commit as a deployment record to Larashed';

    /**
     * @throws \Larashed\Agent\Api\LarashedApiException
     */
    public function handle()
    {
        if (!Agent::isEnabled()) {
            $this->error('Larashed agent is disabled for this environment');
            exit;
        }

        $this->measurements = app(Measurements::class);
        $this->api = app(LarashedApi::class);

        try {
            $this->sendDeployment();
        } catch (LarashedApiException $exception) {
            $this->error('Failed to send deployment data: ' . $exception->getMessage());
        }

        $this->signalAgentQuitForUpdate();
    }

    /**
     * Check if installed
     */
    protected function signalAgentQuitForUpdate()
    {
        $agent = app(GoAgent::class);
        if ($agent->isInstalled() && $agent->hasUpdate()) {
            $agent->signalQuit();
        }
    }

    /**
     * @throws \Larashed\Agent\Api\LarashedApiException
     */
    protected function sendDeployment()
    {
        $deployment = $this->getDeploymentInformation();
        if (!is_null($deployment)) {
            $this->api->sendEnvironmentDeployment($deployment);
        }
    }

    /**
     * @param $command
     * @param $multiline
     *
     * @return array|string
     */
    protected function exec($command, $multiline = false)
    {
        $output = shell_exec($command . ' 2>&1');

        if ($multiline) {
            $output = explode("\n", $output);
        }

        return $output;
    }

    /**
     * @return array|null
     */
    protected function getDeploymentInformation()
    {
        $errors = ['not a git repository', 'not found'];
        if (Str::contains($this->exec('git'), $errors)) {
            return null;
        }

        // "signer": "%GS",
        $format = '{%n  "commit": "%H",%n  "message": "%s",%n  "author": "%aN - %aE",%n  "created_at": %at%n}%n';
        $command = 'git log -1 --pretty=format:"' . addslashes($format) . '"';

        $output = $this->exec($command);
        $output = preg_replace_callback('/message\"\: "(.*)",/i', function ($matches) {
            return 'message": "' . addcslashes($matches[1], '"') . '",';
        }, $output);

        $data = json_decode($output, true);
        if (!$data) {
            return null;
        }

        return [
            'commit_hash'       => Arr::get($data, 'commit'),
            'commit_remote'     => trim($this->exec('git config --get remote.origin.url')),
            'commit_message'    => Arr::get($data, 'message'),
            'commit_author'     => Arr::get($data, 'author'),
            'commit_created_at' => $this->measurements->time(Arr::get($data, 'created_at')),
            'created_at'        => $this->measurements->time()
        ];
    }
}
