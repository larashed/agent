<?php

namespace Larashed\Agent;

/**
 * Class Config
 *
 * @package Larashed\Agent\Api
 */
class AgentConfig
{
    const API_VERSION = 'v1';
    const SOCKET_FILE = 'larashed.sock';

    /**
     * @var string
     */
    protected $applicationId;

    /**
     * @var string
     */
    protected $applicationKey;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var string
     */
    protected $storageDirectory;

    /**
     * @var string
     */
    protected $socketFile;

    /**
     * @var string
     */
    protected $socketDirectory;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var bool
     */
    protected $useCertificate;

    /**
     * AgentConfig constructor.
     *
     * @param string $applicationId
     * @param string $applicationKey
     * @param string $environment
     * @param string $storageDirectory
     * @param string $url
     * @param bool   $cert
     */
    public function __construct(
        $applicationId,
        $applicationKey,
        $environment,
        $storageDirectory,
        $url,
        $cert
    )
    {
        $this->applicationId = $applicationId;
        $this->applicationKey = $applicationKey;
        $this->environment = $environment;
        $this->storageDirectory = $storageDirectory;
        $this->url = $url;
        $this->useCertificate = $cert;
    }

    /**
     * @return string
     */
    public function getApplicationId()
    {
        return $this->applicationId;
    }

    /**
     * @return string
     */
    public function getApplicationKey()
    {
        return $this->applicationKey;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return string
     */
    public function getBaseApiUrl()
    {
        return rtrim($this->url, '/') . '/';
    }

    /**
     * @return bool
     */
    public function shouldUseCertificate()
    {
        return $this->useCertificate;
    }

    /**
     * @return string
     */
    public function getStorageDirectory()
    {
        $storageDirectory = rtrim($this->storageDirectory, '/') . '/';

        return $storageDirectory;
    }

    /**
     * @return string
     */
    public function getGoAgentDirectory()
    {
        return $this->getStorageDirectory() . 'bin/';
    }

    /**
     * @return string
     */
    public function getGoAgentPath()
    {
        return $this->getGoAgentDirectory() . 'agent';
    }

    /**
     * @param $tag
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getGoAgentDownloadUrl($tag)
    {
        $binary = $this->goBinaryName();

        return 'https://github.com/larashed/agent-go/releases/download/' . $tag . '/' . $binary;
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function getGoAgentLatestDownloadUrl()
    {
        $binary = $this->goBinaryName();

        return 'https://github.com/larashed/agent-go/releases/latest/download/' . $binary;
    }

    /**
     * @return string
     */
    public function getGoAgentLatestVersionUrl()
    {
        return 'https://api.github.com/repos/larashed/agent-go/releases/latest';
    }

    /**
     * @return array
     */
    public function getIgnoredEndpoints()
    {
        return config('larashed.ignored_endpoints', []);
    }

    /**
     * @return string
     */
    protected function goBinaryName()
    {
        if (strtolower(PHP_OS) === 'darwin') {
            return 'agent_darwin_amd64';
        }

        return 'agent_linux_amd64';
    }
}
