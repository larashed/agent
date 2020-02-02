<?php

namespace Larashed\Agent\Api;

use Illuminate\Support\Collection;
use Larashed\Agent\AgentConfig;

/**
 * Class LarashedApi
 *
 * @package Larashed\Agent\Api
 */
class LarashedApi
{
    /**
     * @var AgentConfig
     */
    protected $config;

    /**
     * LarashedApi constructor.
     *
     * @param AgentConfig $config
     */
    public function __construct(AgentConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param $data
     *
     * @return mixed
     * @throws LarashedApiException
     */
    public function sendEnvironmentDeployment($data)
    {
        return $this->makePostRequest('agent/environment/deployment', $data);
    }

    /**
     * @param $data
     *
     * @return mixed
     *
     * @throws LarashedApiException
     */
    public function sendEnvironmentInformation($data)
    {
        return $this->makePostRequest('agent/environment/information', $data);
    }

    /**
     * @param $data
     *
     * @return mixed
     *
     * @throws LarashedApiException
     */
    public function sendServerInformation($data)
    {
        return $this->makePostRequest('agent/server/information', $data);
    }

    /**
     * @param       $endpoint
     * @param mixed $body
     *
     * @return mixed
     *
     * @throws LarashedApiException
     */
    protected function makePostRequest($endpoint, $body)
    {
        $uri = rtrim($this->config->getBaseApiUrl(), '/') . '/' . AgentConfig::API_VERSION . '/' . $endpoint;
        $authKey = $this->config->getApplicationId() . ":" . $this->config->getApplicationKey();
        $headers = ['Larashed-Environment: ' . $this->config->getEnvironment()];
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Accept: application/json';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_USERPWD, $authKey);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->normalizeBody($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->config->shouldUseCertificate());

        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response, true);

        if (is_null($json)) {
            throw new LarashedApiException('Failed to send request to Larashed API: ' . $response);
        }

        return $json;
    }

    /**
     * @param $body
     *
     * @return string
     */
    protected function normalizeBody($body)
    {
        if (is_array($body) || $body instanceof Collection) {
            return json_encode($body);
        }

        return $body;
    }
}
