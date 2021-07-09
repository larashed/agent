<?php

namespace Larashed\Agent\Trackers\Queue;

use Exception;
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
     * @var string
     */
    protected $id;

    /**
     * @var string
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
     * @var string
     */
    protected $workerId;

    /**
     * Job constructor.
     *
     * @param Measurements $measurements
     * @param JobContract  $job
     * @param string       $workerId
     */
    public function __construct(Measurements $measurements, JobContract $job, $workerId)
    {
        $this->measurements = $measurements;
        $this->job = $this->setAttributes($job);
        $this->workerId = $workerId;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'worker_id'    => $this->workerId,
            'worker_pid'   => getmypid(),
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
        $this->id = $job->getJobId();
        $this->name = $job->resolveName();
        $this->attempts = $job->attempts();
        $this->setStartedAt($this->measurements->microtime());
        $this->setCreatedAt($this->measurements->militime());
        $this->setStartMemoryUsage($this->measurements->memory());

        return $job;
    }
}
