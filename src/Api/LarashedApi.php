<?php

namespace Larashed\Agent\Api;

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
    public function sendAgentData($data)
    {
        return $this->makeRequest('agent', ['batch' => true], $data);
    }

    /**
     * @param       $endpoint
     * @param array $query
     * @param null  $body
     *
     * @return mixed
     *
     * @throws LarashedApiException
     */
    protected function makeRequest($endpoint, $query = [], $body = null)
    {
        $uri = $this->config->getBaseUrl() . '/' . $endpoint . '?' . http_build_query($query);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt(
            $ch,
            CURLOPT_USERPWD,
            $this->config->getApplicationId() . ":" . $this->config->getApplicationKey()
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->config->shouldUseCertificate());
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response, true);

        if (is_null($json)) {
            throw new LarashedApiException('Failed to make request to Larashed API');
        }

        return $json;
    }
}
