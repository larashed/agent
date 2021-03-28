<?php

namespace Larashed\Agent\Queue\Queues;

use Illuminate\Queue\BeanstalkdQueue as BaseQueue;
use Larashed\Agent\Events\JobDispatched;
use Pheanstalk\Pheanstalk;

class BeanstalkdQueue extends BaseQueue
{
    use DispatchEventTrait;

    /**
     * Push a raw payload onto the queue.
     *
     * @param string      $payload
     * @param string|null $queue
     * @param array       $options
     *
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $job = $this->pheanstalk->useTube($this->getQueue($queue))->put(
            $payload, Pheanstalk::DEFAULT_PRIORITY, Pheanstalk::DEFAULT_DELAY, $this->timeToRun
        );

        $this->dispatchEvent(new JobDispatched($job->getId(), $this->getConnectionName(), $this->getQueue($queue)));

        return $job;
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param \DateTimeInterface|\DateInterval|int $delay
     * @param string                               $job
     * @param mixed                                $data
     * @param string|null                          $queue
     *
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->enqueueUsing(
            $job,
            $this->createPayload($job, $this->getQueue($queue), $data),
            $queue,
            $delay,
            function ($payload, $queue, $delay) {
                $job =  $this->pheanstalk->useTube($this->getQueue($queue))->put(
                    $payload,
                    Pheanstalk::DEFAULT_PRIORITY,
                    $this->secondsUntil($delay),
                    $this->timeToRun
                );

                $this->dispatchEvent(new JobDispatched($job->getId(), $this->getConnectionName(), $this->getQueue($queue)));

                return $job;
            }
        );
    }
}
