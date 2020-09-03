<?php

namespace Larashed\Agent\Tests\Unit\Trackers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Larashed\Agent\Events\RequestExecuted;
use Larashed\Agent\Tests\Traits\RequestMock;
use Larashed\Agent\Trackers\HttpRequestTracker;
use Orchestra\Testbench\TestCase;
use Larashed\Agent\Tests\Traits\MeasurementsMock;

class HttpRequestTrackerTest extends TestCase
{
    use MeasurementsMock, RequestMock;

    public function setUp()
    {
        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', 0);
        }

        parent::setUp();
    }

    public function testHttpRequestTrackerBindsAndTracksEvents()
    {
        $tracker = new HttpRequestTracker(app(Dispatcher::class), $this->getMeasurementsMock());
        $tracker->bind();

        event($this->getRequestExecutedEvent());

        $this->assertNotEmpty($tracker->gather());
    }

    public function testHttpRequestTrackerCleansUp()
    {
        $tracker = new HttpRequestTracker(app(Dispatcher::class), $this->getMeasurementsMock());
        $tracker->bind();

        event($this->getRequestExecutedEvent());

        $this->assertNotEmpty($tracker->gather());

        $tracker->cleanup();

        $this->assertEmpty($tracker->gather());
    }

    protected function getRequestExecutedEvent()
    {
        $request = $this->getRequestMock($this->getRouteMock(), $this->getUserMock());
        $mock = new RequestExecuted($request, $this->getResponseMock(), 0);

        return $mock;
    }
}
