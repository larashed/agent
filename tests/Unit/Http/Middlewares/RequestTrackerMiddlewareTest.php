<?php

namespace Larashed\Agent\Tests\Unit\Http\Middlewares;

use Illuminate\Contracts\Events\Dispatcher;
use Larashed\Agent\Agent;
use Larashed\Agent\AgentConfig;
use Larashed\Agent\Events\RequestExecuted;
use Larashed\Agent\Http\Middlewares\RequestTrackerMiddleware;
use Larashed\Agent\Tests\Traits\RequestMock;
use Orchestra\Testbench\TestCase;

class RequestTrackerMiddlewareTest extends TestCase
{
    use RequestMock;

    public function testHandleTriggersRequestExecutedEvent()
    {
        $agent = \Mockery::mock(Agent::class, [
            'stop' => null
        ]);
        $config = \Mockery::mock(AgentConfig::class, [
            'getIgnoredEndpoints' => []
        ]);
        $called = false;

        $dispatcher = app(Dispatcher::class);
        $dispatcher->listen(RequestExecuted::class, function () use (&$called) {
            $called = true;
        });

        $request = $this->getRequestMock($this->getRouteMock(), $this->getUserMock());

        $next = function () {
            return $this->getResponseMock();
        };

        $middleware = new RequestTrackerMiddleware($agent, $config);

        $this->assertFalse($called);

        $middleware->handle($request, $next);

        $this->assertTrue($called);
    }

    public function testHandleIgnoresRequest()
    {
        $agent = \Mockery::mock(Agent::class, [
            'stop' => null
        ]);
        $config = \Mockery::mock(AgentConfig::class, [
            'getIgnoredEndpoints' => ['/lara']
        ]);
        $called = false;

        $dispatcher = app(Dispatcher::class);
        $dispatcher->listen(RequestExecuted::class, function () use (&$called) {
            $called = true;
        });

        $request = $this->getRequestMock($this->getRouteMock(), $this->getUserMock(), 'http://host/something/lara/shed');

        $next = function () {
            return $this->getResponseMock();
        };

        $middleware = new RequestTrackerMiddleware($agent, $config);

        $this->assertFalse($called);

        $middleware->handle($request, $next);

        $this->assertFalse($called);
    }

    public function testTerminateCallsAgentStop()
    {
        $called = false;
        $agent = \Mockery::mock(Agent::class);
        $agent->shouldReceive('stop')->andReturnUsing(function () use (&$called) {
            $called = true;
        });
        $config = \Mockery::mock(AgentConfig::class, [
            'getIgnoredEndpoints' => null
        ]);
        $middleware = new RequestTrackerMiddleware($agent, $config);

        $this->assertFalse($called);

        $middleware->terminate(null, null);

        $this->assertTrue($called);
    }
}
