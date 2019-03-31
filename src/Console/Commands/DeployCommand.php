<?php

namespace Larashed\Agent\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Larashed\Agent\Api\LarashedApi;
use Larashed\Agent\Api\LarashedApiException;
use Larashed\Agent\Console\DaemonRestartHandler;
use Larashed\Agent\System\Measurements;
use Larashed\Agent\System\System;

/**
 * Class DeployCommand
 *
 * @package Larashed\Agent\Console\Commands
 */
class DeployCommand extends Command
{
    /**
     * @var System
     */
    protected $system;

    /**
     * @var DaemonRestartHandler
     */
    protected $daemonRestart;

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
    protected $description = 'Handles application deployment for larashed:daemon';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(System $system, Measurements $measurements, DaemonRestartHandler $daemonRestart, LarashedApi $api)
    {
        $this->system = $system;
        $this->measurements = $measurements;
        $this->daemonRestart = $daemonRestart;
        $this->api = $api;

        parent::__construct();
    }

    /**
     * @throws \Larashed\Agent\Api\LarashedApiException
     */
    public function handle()
    {
        $this->daemonRestart->markNeeded();

        try {
            $this->sendDeployment();
        } catch (LarashedApiException $exception) {
            $this->error('Failed to send deployment data: ' . $exception->getMessage());
        }
    }

    /**
     * @throws \Larashed\Agent\Api\LarashedApiException
     */
    protected function sendDeployment()
    {
        $deployment = $this->getDeploymentInformation();

        if (!is_null($deployment)) {
            $this->api->sendDeploymentData($deployment);
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
        $output = $this->system->exec($command . ' 2>&1');

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
        if (str_contains($this->exec('git'), $errors)) {
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
            'commit_hash'       => array_get($data, 'commit'),
            'commit_remote'     => trim($this->exec('git config --get remote.origin.url')),
            'commit_message'    => array_get($data, 'message'),
            'commit_author'     => array_get($data, 'author'),
            'commit_created_at' => $this->measurements->time(array_get($data, 'commit_created_at')),
            'created_at'        => $this->measurements->time()
        ];
    }
}
