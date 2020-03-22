<?php

namespace Larashed\Agent\Tests\Unit\Trackers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Larashed\Agent\Events\RequestExecuted;
use Larashed\Agent\Tests\Traits\RequestMock;
use Larashed\Agent\Trackers\HttpRequestTracker;
use Larashed\Agent\Trackers\LogTracker;
use Orchestra\Testbench\TestCase;
use Larashed\Agent\Tests\Traits\MeasurementsMock;

class LogTrackerTest extends TestCase
{
    protected $eventName = 'Illuminate\Log\Events\MessageLogged';

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testLogTrackerBindsAndTracksEvent()
    {
        $tracker = new LogTracker(app(Dispatcher::class));
        $tracker->bind();

        event($this->getMessageLoggedEvent());

        $log = $tracker->gather();

        $this->assertNotEmpty($log);
        $this->assertEquals(1, $log[0]['order']);
        $this->assertEquals('debug', $log[0]['level']);
        $this->assertEquals('something.happened', $log[0]['message']);
        $this->assertEquals([1, 2, 3], $log[0]['context']);
    }

    public function testLogTrackerCleansUp()
    {
        $tracker = new LogTracker(app(Dispatcher::class));
        $tracker->bind();

        event($this->getMessageLoggedEvent());

        $this->assertNotEmpty($tracker->gather());

        $tracker->cleanup();

        $this->assertEmpty($tracker->gather());
    }

    protected function getMessageLoggedEvent()
    {
        if (class_exists($this->eventName)) {
            $event = new $this->eventName('debug', 'something.happened', [1, 2, 3]);
        }

        return $event;
    }
}
