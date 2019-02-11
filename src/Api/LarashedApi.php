<?php

namespace Larashed\Agent\Api;

use Illuminate\Support\Collection;

/**
 * Class LarashedApi
 *
 * @package Larashed\Agent\Api
 */
class LarashedApi
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * LarashedApi constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param $data
     *
     * @return mixed
     *
     * @throws LarashedApiException
     */
    public function sendAppData($data)
    {
        return $this->makePostRequest('agent/application', $data);
    }

    /**
     * @param $data
     *
     * @return mixed
     *
     * @throws LarashedApiException
     */
    public function sendServerData($data)
    {
        return $this->makePostRequest('agent/server', $data);
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
        $uri = rtrim($this->config->getBaseUrl(), '/') . '/' . $endpoint;
        $authKey = $this->config->getApplicationId() . ":" . $this->config->getApplicationKey();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_USERPWD, $authKey);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->normalizeBody($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Larashed-Environment: ' . $this->config->getEnvironment()]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->config->shouldUseCertificate());

        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response, true);
        if (is_null($json)) {
            throw new LarashedApiException('Failed to make request to Larashed API');
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
