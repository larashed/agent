<?php

namespace Larashed\Agent\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Larashed\Agent\AgentConfig;
use Symfony\Component\Process\Process;

/**
 * Class GoAgent
 * @package Larashed\Agent\Console
 */
class GoAgent
{
    /**
     * @var AgentConfig
     */
    protected $config;

    /**
     * @var Command
     */
    protected $command;

    /**
     * GoAgent constructor.
     *
     * @param AgentConfig $config
     * @param Command     $command
     */
    public function __construct(AgentConfig $config, Command $command)
    {
        $this->config = $config;
        $this->command = $command;
    }

    /**
     * Run agent-go daemon
     */
    public function runDaemonCommand()
    {
        $process = new Process($this->getDaemonCommandArguments(), null, null, null, null);
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->command->error(trim($buffer));
            } else {
                $this->command->info(trim($buffer));
            }
        });

        $this->command->info($process->getOutput());
        $this->command->error($process->getErrorOutput());
    }

    /**
     * Run agent-go version --json command
     */
    public function getInstalledVersionTag()
    {
        $process = new Process($this->getVersionCommandArguments(), null, null, null, null);
        $process->run();

        $output = json_decode(trim($process->getOutput()), true);
        if ($output) {
            return $output['tag'];
        }

        return null;
    }

    /**
     * Install, upgrade and configure agent-go executable
     */
    public function installOrUpgrade()
    {
        $latestVersion = $this->getLatestVersionTag();

        if (file_exists($this->config->getGoAgentPath())) {
            $this->command->line("Found an existing agent binary. Checking if upgrade is needed.");

            $installedVersion = $this->getInstalledVersionTag();
            if ($latestVersion == $installedVersion) {
                $this->command->line("Installed agent is already at the latest (v" . $installedVersion . ") version.");

                return;
            }

            $this->command->line("Starting upgrade.");
        }

        if (!is_dir($this->config->getGoAgentDirectory())) {
            $this->command->line("Agent executable directory doesn't exist. Creating.");

            if (!mkdir($this->config->getGoAgentDirectory(), 0777, true)) {
                $this->command->error("Failed to create " . $this->config->getGoAgentDirectory() . " dir for agent executable.");

                return;
            }
        }

        if (file_exists($this->config->getGoAgentPath())) {
            $this->command->line("Removing old version.");
            if (!unlink($this->config->getGoAgentPath())) {
                $this->command->line("Failed to delete the old version.");

                return;
            }
        }

        $this->command->line("Downloading agent v" . $latestVersion);

        $agentUrl = $this->config->getGoAgentDownloadUrl($latestVersion);
        if (!copy($agentUrl, $this->config->getGoAgentPath())) {
            $this->command->error("Failed to download agent.");

            return;
        }

        $this->command->line("Downloaded.");
        $this->command->line("Setting permissions.");

        if (!chmod($this->config->getGoAgentPath(), 0777)) {
            $this->command->error("Failed to set permissions on agent executable.");

            return;
        }

        $this->command->line("Done. Agent binary ready.");
    }

    /**
     * @return mixed|string
     */
    protected function getLatestVersionTag()
    {
        $tag = 'latest';

        if ($response = $this->doGetRequest($this->config->getGoAgentLatestVersionUrl())) {
            $version = json_decode($response, true);

            $tag = Arr::get($version, 'tag_name');
        }

        return $tag;
    }


    /**
     * @return array
     */
    protected function getVersionCommandArguments()
    {
        $command = 'version';

        $arguments = [
            $this->config->getGoAgentPath(),
            $command,
            '--json',
        ];

        return $arguments;
    }

    /**
     * @return array
     */
    protected function getDaemonCommandArguments()
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
        ];

        return $arguments;
    }

    protected function doGetRequest($url)
    {
        // Get cURL resource
        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL            => $url,
            CURLOPT_USERAGENT      => 'Larashed/Agent v1.0'
        ]);
        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        curl_close($curl);

        return $resp;
    }
}
