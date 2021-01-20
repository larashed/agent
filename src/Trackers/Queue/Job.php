<?php

namespace Larashed\Agent\Trackers\Queue;

use Illuminate\Contracts\Queue\Job as JobContract;
use Larashed\Agent\System\Measurements;
use Larashed\Agent\Trackers\Traits\ExceptionTransformerTrait;
use Larashed\Agent\Trackers\Traits\MemoryCalculationTrait;
use Larashed\Agent\Trackers\Traits\TimeCalculationTrait;
use Throwable;

/**
 * Class Job
 *
 * @package Larashed\Agent\Trackers\Queue
 */
class Job
{
    use MemoryCalculationTrait,
        TimeCalculationTrait,
        ExceptionTransformerTrait;

    /**
     * @var JobContract
     */
    protected $job;

    /**
     * @var Measurements
     */
    protected $measurements;

    /**
     * @var array
     */
    protected $name;

    /**
     * @var int
     */
    protected $attempts;

    /**
     * @var string
     */
    protected $connection;

    /**
     * @var string
     */
    protected $queue;

    /**
     * @var int
     */
    protected $queueSize = 0;

    /**
     * @var
     */
    protected $workerPid;

    /**
     * Job constructor.
     *
     * @param Measurements $measurements
     * @param JobContract $job
     */
    public function __construct(Measurements $measurements, JobContract $job)
    {
        $this->measurements = $measurements;
        $this->job = $this->setAttributes($job);
    }

    /**
     * @param int $queueSize
     */
    public function setQueueSize(int $queueSize)
    {
        $this->queueSize = $queueSize;
    }

    /**
     * @param mixed $workerPid
     */
    public function setWorkerPid($workerPid)
    {
        $this->workerPid = $workerPid;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name'         => $this->name,
            'worker_pid'   => $this->workerPid,
            'attempts'     => $this->attempts,
            'connection'   => $this->connection,
            'queue'        => $this->queue,
            'queue_size'   => $this->queueSize,
            'created_at'   => $this->createdAt,
            'processed_in' => $this->processedIn,
            'memory'       => $this->memory,
            'exception'    => $this->exception
        ];
    }

    /**
     * @param                $connection
     * @param Throwable|null $exception
     *
     * @throws Throwable
     */
    public function finalize($connection, Throwable $exception = null)
    {
        $this->connection = $connection;
        $this->queue = $this->job->getQueue();
        $this->setProcessedIn($this->measurements->microtime());
        $this->setMemoryUsage($this->measurements->memory());

        if (!is_null($exception)) {
            $this->setExceptionData($exception);
        }
    }

    /**
     * @param JobContract $job
     *
     * @return JobContract
     */
    protected function setAttributes(JobContract $job)
    {
        $this->name = $job->resolveName();
        $this->attempts = $job->attempts();
        $this->setStartedAt($this->measurements->microtime());
        $this->setCreatedAt($this->measurements->time());
        $this->setStartMemoryUsage($this->measurements->memory());

        return $job;
    }
}
