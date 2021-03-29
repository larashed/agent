<?php

namespace Larashed\Agent\Queue\Queues;

use Illuminate\Queue\SqsQueue as BaseQueue;
use Larashed\Agent\Events\JobDispatched;

class SqsQueue extends BaseQueue
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
        $id = $this->sqs->sendMessage([
            'QueueUrl' => $this->getQueue($queue), 'MessageBody' => $payload,
        ])->get('MessageId');

        $this->dispatchEvent(new JobDispatched(
            $id,
            $this->getConnectionName(),
            $this->formatQueueName($this->getQueue($queue))
        ));

        return $id;
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
            $this->createPayload($job, $queue ?: $this->default, $data),
            $queue,
            $delay,
            function ($payload, $queue, $delay) {
                $id = $this->sqs->sendMessage([
                    'QueueUrl'     => $this->getQueue($queue),
                    'MessageBody'  => $payload,
                    'DelaySeconds' => $this->secondsUntil($delay),
                ])->get('MessageId');

                $this->dispatchEvent(new JobDispatched(
                    $id,
                    $this->getConnectionName(),
                    $this->formatQueueName($this->getQueue($queue)),
                    $this->availableAt($delay)
                ));

                return $id;
            }
        );
    }

    /**
     * @param $queue
     *
     * @return string
     */
    protected function formatQueueName($queue)
    {
        return basename($queue);
    }
}
