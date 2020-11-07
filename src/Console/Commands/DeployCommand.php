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
    const INFORMATION_FILE = '.larashed.json';

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
    protected $signature = 'larashed:deploy {--create : Create a deployment information file}';

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

        // create deployment information file
        // useful for Docker containers since no .git directory is present during runtime
        if ($this->option('create')) {
            $this->createDeploymentFile();

            $this->info('Deployment information stored in .larashed.json');

            return;
        }

        $this->api = app(LarashedApi::class);

        try {
            $this->sendDeployment();
            $this->info('Sent deployment information.');
        } catch (LarashedApiException $exception) {
            $this->error('Failed to send deployment data: ' . $exception->getMessage());
        }

        $this->signalAgentQuitForUpdate();
    }

    /**
     * Notify the agent to quit for an update
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
        $deployment = null;

        if (file_exists($this->getInformationFilePath())) {
            $contents = json_decode(file_get_contents($this->getInformationFilePath()), true);
            if (!is_null($contents) && isset($contents['deployment'])) {
                $deployment = $contents['deployment'];
            } else {
                $this->error('Failed to read deployment information from ' . self::INFORMATION_FILE);
            }
        } else {
            $deployment = $this->getDeploymentInformationFromGit();
        }

        if (!is_null($deployment)) {
            $this->api->sendApplicationDeployment($deployment);
        }
    }

    /**
     * @throws \Larashed\Agent\Api\LarashedApiException
     */
    protected function createDeploymentFile()
    {
        $deployment = $this->getDeploymentInformationFromGit();
        unset($deployment['created_at']);

        $file = [
            'deployment' => $deployment
        ];

        file_put_contents(base_path(self::INFORMATION_FILE), json_encode($file, JSON_PRETTY_PRINT));
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
    protected function getDeploymentInformationFromGit()
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

    protected function getInformationFilePath()
    {
        return base_path(self::INFORMATION_FILE);
    }
}
