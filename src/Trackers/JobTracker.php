<?php

namespace Larashed\Agent\Trackers;

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
                'job_name'   => $event->job->resolveName(),
                'started_at' => microtime(true),
                'memory'     => memory_get_usage(false),
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
        $meta['started_at'] = $this->toDate($meta['started_at']);
        $meta['ended_at'] = $this->toDate(microtime(true));
        $meta['memory'] = memory_get_usage(false) - $meta['memory'];

        return $meta;
    }

    protected function toDate($microtime)
    {
        $milliseconds = sprintf("%03d", ($microtime - floor($microtime)) * 1000);

        return date('Y-m-d H:i:s.' . $milliseconds, $microtime);
    }
}
