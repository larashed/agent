<?php

namespace Larashed\Agent\Tests\Unit\Trackers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\WorkerStopping;
use Illuminate\Queue\WorkerOptions;
use Larashed\Agent\Api\LarashedApi;
use Larashed\Agent\Events\WorkerStarting;
use Larashed\Agent\Tests\Traits\RequestMock;
use Larashed\Agent\Trackers\QueueWorkerTracker;
use Orchestra\Testbench\TestCase;
use Larashed\Agent\Tests\Traits\MeasurementsMock;

class QueueWorkerTrackerTest extends TestCase
{
    use MeasurementsMock, RequestMock;

    protected $startCalled = false;
    protected $stopCalled = false;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testQueueWorkerTrackerBindsAndTracksWorkerStoppingEvent()
    {
        $tracker = new QueueWorkerTracker(app(Dispatcher::class), $this->getMeasurementsMock(), $this->getApiMock());
        $tracker->bind();

        event(new WorkerStarting('1', '2', new WorkerOptions()));
        event(new WorkerStopping(1));

        $this->assertTrue($this->startCalled);
        $this->assertTrue($this->stopCalled);
    }

    protected function getApiMock()
    {
        $api = \Mockery::mock(LarashedApi::class);
        $api->shouldReceive('sendQueueWorkerStartEvent')->andReturnUsing(function () {
            $this->startCalled = true;
        });
        $api->shouldReceive('sendQueueWorkerStopEvent')->andReturnUsing(function () {
            $this->stopCalled = true;
        });

        return $api;
    }
}
