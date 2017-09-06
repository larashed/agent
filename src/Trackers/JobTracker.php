<?php

namespace Larashed\Agent\Trackers;

use Carbon\Carbon;
use Queue;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

class JobTracker extends BaseTracker
{
    public function bind()
    {
        Queue::before(function (JobProcessing $event) {
            $meta = [
                'name'       => $event->job->resolveName(),
                'started_at' => microtime(true),
                'memory'     => memory_get_usage(false)
            ];

            $event->job->larashedMetaData = $meta;
        });

        Queue::after(function (JobProcessed $event) {
            $this->agent->getCollector()->addJob($this->render($event));
            $this->agent->terminate();
        });

        Queue::failing(function (JobFailed $event) {
            $this->agent->getCollector()->addFailedJob($this->render($event));
            $this->agent->terminate();
        });

        return $this;
    }

    protected function render($event)
    {
        $meta = $event->job->larashedMetaData;
        $meta['connection'] = $event->connectionName;
        $meta['queue'] = $event->job->getQueue();
        $meta['created_at'] = Carbon::createFromTimestampUTC($meta['started_at'])->format('c');
        $meta['attempts'] = $event->job->attempts();
        $meta['processed_in'] = round((microtime(true) - $meta['started_at']) * 1000, 2);
        $meta['memory'] = memory_get_usage(false) - $meta['memory'];

        return $meta;
    }
}
