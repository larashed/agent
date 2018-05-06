<?php

namespace Larashed\Agent\Trackers\Database;

/**
 * Class QueryExcluderConfig
 *
 * @package Larashed\Agent\Trackers\Database
 */
class QueryExcluderConfig
{
    /**
     * @var string
     */
    protected $queueDriver;

    /**
     * @var
     */
    protected $failedJobTable;

    /**
     * @var array
     */
    protected $jobTable;

    /**
     * QueryExcluderOptions constructor.
     *
     * @param string $queueDriver
     * @param string $jobTable
     * @param string $failedJobTable
     */
    public function __construct($queueDriver, $jobTable, $failedJobTable)
    {
        $this->queueDriver = $queueDriver;
        $this->jobTable = $jobTable;
        $this->failedJobTable = $failedJobTable;
    }

    /**
     * @return string
     */
    public function getQueueDriver()
    {
        return $this->queueDriver;
    }

    /**
     * @return array|string
     */
    public function getJobTable()
    {
        return $this->jobTable;
    }

    /**
     * @return string
     */
    public function getFailedJobTable()
    {
        return $this->failedJobTable;
    }

    /**
     * @return static
     */
    public static function fromConfig()
    {
        return new static(
            config('queue.default'),
            config('queue.connections.' . config('queue.default') . '.table'),
            config('queue.failed.table')
        );
    }
}
