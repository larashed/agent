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
     * @return array
     * @throws LarashedApiException
     */
    public function sendApplicationDeployment($data)
    {
        return $this->makePostRequest('agent/app/deployment', $data);
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws LarashedApiException
     */
    public function sendQueueWorkerStartEvent($data)
    {
        return $this->makePostRequest('agent/app/queue-worker/start', $data);
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws LarashedApiException
     */
    public function sendQueueWorkerStopEvent($data)
    {
        return $this->makePostRequest('agent/app/queue-worker/stop', $data);
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws LarashedApiException
     */
    public function sendQueueWorkerPing($data)
    {
        return $this->makePostRequest('agent/app/queue-worker/ping', $data);
    }

    /**
     * @param       $endpoint
     * @param mixed $body
     *
     * @return array
     *
     * @throws LarashedApiException
     */
    protected function makePostRequest($endpoint, $body)
    {
        $uri = rtrim($this->config->getBaseApiUrl(), '/') . '/' . AgentConfig::API_VERSION . '/' . $endpoint;
        $authKey = $this->config->getApplicationId() . ":" . $this->config->getApplicationKey();

        $headers = ['Larashed-Environment: ' . $this->config->getEnvironment()];
        $headers[] = 'User-Agent: Larashed/PHPAgent';
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
