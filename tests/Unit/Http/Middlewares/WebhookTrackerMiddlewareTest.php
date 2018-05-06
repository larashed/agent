<?php

namespace Larashed\Agent\Tests\Unit\Http\Middlewares;

use Illuminate\Contracts\Events\Dispatcher;
use Larashed\Agent\Agent;
use Larashed\Agent\Events\RequestExecuted;
use Larashed\Agent\Events\WebhookExecuted;
use Larashed\Agent\Http\Middlewares\RequestTrackerMiddleware;
use Larashed\Agent\Http\Middlewares\WebhookTrackerMiddleware;
use Larashed\Agent\Tests\Traits\RequestMock;
use Orchestra\Testbench\TestCase;

class WebhookTrackerMiddlewareTest extends TestCase
{
    use RequestMock;

    public function testHandleTriggersWebhookExecutedEvent()
    {
        $called = false;

        $dispatcher = app(Dispatcher::class);
        $dispatcher->listen(WebhookExecuted::class, function (WebhookExecuted $event) use (&$called) {
            $called = true;
            $this->assertEquals('some-name', $event->name);
            $this->assertEquals('some-source', $event->source);
        });

        $request = $this->getRequestMock($this->getRouteMock(), $this->getUserMock());

        $next = function () {
            return $this->getResponseMock();
        };

        $middleware = new WebhookTrackerMiddleware();

        $this->assertFalse($called);

        $middleware->handle($request, $next, 'some-source', 'some-name');

        $this->assertTrue($called);
    }
}
