<?php

namespace Larashed\Agent\Api;

/**
 * Class Config
 *
 * @package Larashed\Agent\Api
 */
class Config
{
    const API_VERSION = 'v1';

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
    protected $url;

    /**
     * @var bool
     */
    protected $useCertificate;

    /**
     * Config constructor.
     *
     * @param        $applicationId
     * @param        $applicationKey
     * @param        $environment
     * @param string $url
     * @param bool   $cert
     */
    public function __construct($applicationId, $applicationKey, $environment, $url = 'https://api.larashed.com/', $cert = true)
    {
        $this->applicationId = $applicationId;
        $this->applicationKey = $applicationKey;
        $this->environment = $environment;
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
    public function getBaseUrl()
    {
        return rtrim('/', $this->url) . '/' . self::API_VERSION;
    }

    /**
     * @return bool
     */
    public function shouldUseCertificate()
    {
        return $this->useCertificate;
    }
}
