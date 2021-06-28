<?php

namespace Larashed\Agent\Queue\Queues;

use Illuminate\Contracts\Events\Dispatcher;
use Larashed\Agent\Events\JobDispatched;

trait DispatchesEvent
{
    protected function dispatchEvent(JobDispatched $event)
    {
        if ($this->container && $this->container->bound(Dispatcher::class)) {
            $this->container->make(Dispatcher::class)->dispatch($event);
        }
    }
}
