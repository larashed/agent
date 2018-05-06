<?php

namespace Larashed\Agent\Trackers\Queue;

use Exception;
use Illuminate\Contracts\Queue\Job as JobContract;
use Larashed\Agent\System\Measurements;
use Larashed\Agent\Trackers\Traits\ExceptionTransformerTrait;
use Larashed\Agent\Trackers\Traits\MemoryCalculationTrait;
use Larashed\Agent\Trackers\Traits\TimeCalculationTrait;

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
     * @var array
     */
    protected $exception;

    /**
     * Job constructor.
     *
     * @param Measurements $measurements
     */
    public function __construct(Measurements $measurements, JobContract $job)
    {
        $this->measurements = $measurements;
        $this->job = $this->setAttributes($job);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name'         => $this->name,
            'attempts'     => $this->attempts,
            'connection'   => $this->connection,
            'queue'        => $this->queue,
            'created_at'   => $this->createdAt,
            'processed_in' => $this->processedIn,
            'memory'       => $this->memory,
            'exception'    => $this->exception
        ];
    }

    /**
     * @param                $connection
     * @param Exception|null $exception
     *
     * @throws Exception
     */
    public function finalize($connection, Exception $exception = null)
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
