<?php

namespace Larashed\Agent\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Larashed\Agent\AgentConfig;
use Larashed\Agent\Ipc\SocketClient;
use Symfony\Component\Process\Process;

/**
 * Class GoAgent
 *
 * @package Larashed\Agent\Console
 */
class GoAgent
{
    /**
     * @var AgentConfig
     */
    protected $config;

    /**
     * @var string
     */
    protected $latestVersion;

    /**
     * GoAgent constructor.
     *
     * @param AgentConfig $config
     */
    public function __construct(AgentConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Run `agent-go daemon ...`
     *
     * @param $logLevel
     */
    public function run($logLevel)
    {
        $command = 'daemon';

        $arguments = [
            $this->config->getGoAgentPath(),
            $command,
            '--socket=' . $this->config->getSocketPath(),
            '--env=' . $this->config->getEnvironment(),
            '--app-id=' . $this->config->getApplicationId(),
            '--app-key=' . $this->config->getApplicationKey(),
            '--api-url=' . $this->config->getBaseApiUrl(),
            '--log-level=' . $logLevel,
        ];

        $process = new Process($arguments, null, null, null, null);

        try {
            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    $this->print(trim($buffer));
                } else {
                    $this->print(trim($buffer));
                }
            });
        } catch (\Exception $exception) {
            $this->print("Agent quit - " . $exception->getMessage());
        }

        $this->print($process->getErrorOutput());
    }

    public function update()
    {
        $this->signalQuit();

        if (!$this->deleteExistingAgent()) {
            $this->print("Failed to delete existing agent.");

            return;
        }

        if (!$this->download($this->getLatestVersionTag())) {
            $this->print("Failed to download the latest agent.");

            return;
        }

        if (!$this->setPermissions()) {
            $this->print("Failed to set executable permissions");

            return;
        }

        $this->print("Agent binary updated to v" . $this->latestVersion . ".");
    }

    public function install()
    {
        if (!$this->createDirectories()) {
            $this->print("Creating directories.");

            return;
        }

        if (!$this->deleteExistingAgent()) {
            $this->print("Failed to delete existing agent.");

            return;
        }

        if (!$this->download($this->getLatestVersionTag())) {
            $this->print("Failed to download the latest agent.");

            return;
        }

        if (!$this->setPermissions()) {
            $this->print("Failed to set executable permissions");

            return;
        }

        $this->print("Agent (v" . $this->latestVersion . ") binary installed.");
    }

    public function signalQuit()
    {
        /** @var SocketClient $client */
        $client = app(SocketClient::class);
        $client->send(SocketClient::QUIT);
    }

    public function isInstalled()
    {
        return file_exists($this->config->getGoAgentPath());
    }

    public function hasUpdate()
    {
        $latestVersion = $this->getLatestVersionTag();
        $installedVersion = $this->getInstalledAgentVersion();

        return $latestVersion !== $installedVersion;
    }

    public function getLatestVersionTag()
    {
        if (!is_null($this->latestVersion)) {
            return $this->latestVersion;
        }

        if ($response = $this->doGetRequest($this->config->getGoAgentLatestVersionUrl())) {
            $version = json_decode($response, true);

            $this->latestVersion = Arr::get($version, 'tag_name');
        }

        return $this->latestVersion;
    }

    protected function deleteExistingAgent()
    {
        if (file_exists($this->config->getGoAgentPath())) {
            if (!unlink($this->config->getGoAgentPath())) {
                return false;
            }
        }

        return true;
    }

    protected function createDirectories()
    {
        if (!is_dir($this->config->getGoAgentDirectory())) {
            if (!mkdir($this->config->getGoAgentDirectory(), 0777, true)) {
                return false;
            }
        }

        return true;
    }

    protected function setPermissions()
    {
        if (!chmod($this->config->getGoAgentPath(), 0777)) {
            return false;
        }

        return true;
    }

    protected function download($version)
    {
        $url = $this->config->getGoAgentDownloadUrl($version);
        if (!copy($url, $this->config->getGoAgentPath())) {
            return false;
        }

        return true;
    }

    /**
     * Runs `agent version --json` command
     */
    protected function getInstalledAgentVersion()
    {
        $command = 'version';

        $arguments = [
            $this->config->getGoAgentPath(),
            $command,
            '--json',
        ];

        $process = new Process($arguments, null, null, null, null);
        try {
            $process->run();
        } catch (\Exception $exception) {
            $this->print("Version command failed - " . $exception->getMessage());
        }

        $output = json_decode(trim($process->getOutput()), true);
        if ($output) {
            return $output['tag'];
        }

        return null;
    }

    protected function doGetRequest($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL            => $url,
            CURLOPT_USERAGENT      => 'Larashed/Agent v1.0'
        ]);
        $resp = curl_exec($curl);
        curl_close($curl);

        return $resp;
    }

    protected function print($message)
    {
        echo $message, PHP_EOL;
    }
}
