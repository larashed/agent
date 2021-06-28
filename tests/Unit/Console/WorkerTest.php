<?php

namespace Larashed\Agent\Tests\Unit\Console;

use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\Event;
use Larashed\Agent\Api\LarashedApi;
use Larashed\Agent\Console\Worker;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Larashed\Agent\Events\WorkerStarting;
use Orchestra\Testbench\TestCase;

class WorkerTest extends TestCase
{
    protected $eventSent = false;

    public function testWorkerBuildsAndDispatchesEvents()
    {
        $w = new Worker(
            $this->app['queue'],
            $this->app['events'],
            $this->app[ExceptionHandlerContract::class],
            $isDownForMaintenance = function () {
                return $this->app->isDownForMaintenance();
            }
        );
        $w->shouldQuit = true;

        $this->app['events']->listen(WorkerStarting::class, function(WorkerStarting $event) {
            $this->eventSent = true;
        });

        $w->daemon('redis', 'default', new WorkerOptions());

        $this->assertTrue($this->eventSent);
    }
}
