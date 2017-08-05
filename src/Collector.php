<?php

namespace Larashed\Agent;

use Larashed\Agent\Http\Request;
use Larashed\Agent\Http\Response;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Class DataCollector
 *
 * @package Larashed\Admin\App\Libraries\Storage
 */
class Collector
{
    const QUERY_LOGGING      = 1;
    const JOB_LOGGING        = 2;
    const FAILED_JOB_LOGGING = 3;
    const LOG_DATA_LOGGING   = 4;
    const WEBHOOK_LOGGING    = 5;

    protected $queryLoggingEnabled     = true;
    protected $jobLoggingEnabled       = true;
    protected $failedJobLoggingEnabled = true;
    protected $logDataLoggingEnabled   = true;
    protected $webhookLoggingEnabled   = true;

    protected $records = [];

    /**
     * @param $logs
     *
     * @return $this
     */
    public function addLogs($logs)
    {
        if ($this->logDataLoggingEnabled) {
            $this->records['logs'][] = $logs;
        }

        return $this;
    }

    /**
     * @param $query
     *
     * @return $this
     */
    public function addQuery($query)
    {
        if ($this->queryLoggingEnabled) {
            $this->records['queries'][] = $query;
        }

        return $this;
    }

    /**
     * @param $job
     *
     * @return $this
     */
    public function addJob($job)
    {
        if ($this->jobLoggingEnabled) {
            $this->records['jobs'][] = $job;
        }

        return $this;
    }

    /**
     * @param $job
     *
     * @return $this
     */
    public function addFailedJob($job)
    {
        if ($this->failedJobLoggingEnabled) {
            $this->records['failed_jobs'][] = $job;
        }

        return $this;
    }

    /**
     * @param $webhook
     *
     * @return $this
     */
    public function addWebhook($webhook)
    {
        if ($this->webhookLoggingEnabled) {
            $this->records['webhooks'][] = $webhook;
        }

        return $this;
    }

    /**
     * @param $type
     *
     * @return Collector
     */
    public function enable($type)
    {
        return $this->toggle($type, true);
    }

    /**
     * @param Request $request
     *
     * @return $this
     */
    public function setRequest($request)
    {
        $this->records['request'] = (new Request($request))->toArray();

        return $this;
    }

    /**
     * @param $response
     *
     * @return $this
     */
    public function setResponse($response)
    {
        $this->records['response'] = (new Response($response))->toArray();

        return $this;
    }

    /**
     * @param $type
     *
     * @return Collector
     */
    public function disable($type)
    {
        return $this->toggle($type, false);
    }

    /**
     * @param      $type
     * @param bool $enable
     *
     * @return $this
     */
    protected function toggle($type, $enable = true)
    {
        switch ($type) {
            case self::QUERY_LOGGING:
                $this->queryLoggingEnabled = $enable;
                break;
            case self::JOB_LOGGING:
                $this->jobLoggingEnabled = $enable;
                break;
            case self::FAILED_JOB_LOGGING:
                $this->failedJobLoggingEnabled = $enable;
                break;
            case self::LOG_DATA_LOGGING:
                $this->logDataLoggingEnabled = $enable;
                break;
            case self::WEBHOOK_LOGGING:
                $this->webhookLoggingEnabled = $enable;
                break;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = [
            'environment' => config('app.env')
        ];

        $data = array_merge($data, $this->records);

        return $data;
    }

    /**
     * @return bool
     */
    public function hasData()
    {
        $count = collect($this->getData())->sum(function ($items) {
            return count($items);
        });

        return $count > 0;
    }
}
