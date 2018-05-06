<?php

namespace Larashed\Agent\Tests\Unit\Trackers;

use Illuminate\Contracts\Events\Dispatcher;
use Larashed\Agent\Events\WebhookExecuted;
use Larashed\Agent\Tests\Traits\WebhookRequestMock;
use Larashed\Agent\Trackers\WebhookRequestTracker;
use Orchestra\Testbench\TestCase;
use Larashed\Agent\Tests\Traits\MeasurementsMock;

class WebhookRequestTrackerTest extends TestCase
{
    use MeasurementsMock, WebhookRequestMock;

    public function setUp()
    {
        parent::setUp();
    }

    public function testWebhookRequestTrackerBindsAndTracksEvents()
    {
        $tracker = new WebhookRequestTracker(app(Dispatcher::class), $this->getMeasurementsMock());
        $tracker->bind();

        event($this->getWebhookExecutedEvent());

        $this->assertNotEmpty($tracker->gather());
    }

    public function testWebhookRequestTrackerCleansUp()
    {
        $tracker = new WebhookRequestTracker(app(Dispatcher::class), $this->getMeasurementsMock());
        $tracker->bind();

        event($this->getWebhookExecutedEvent());

        $this->assertNotEmpty($tracker->gather());

        $tracker->cleanup();

        $this->assertEmpty($tracker->gather());
    }

    protected function getWebhookExecutedEvent()
    {
        $mock = new WebhookExecuted($this->getWebhookRequestMock(), 'source', 'name');

        return $mock;
    }
}
