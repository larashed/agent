<?php

namespace Larashed\Agent\Tests\Integration;

use Illuminate\Support\Facades\Artisan;
use Larashed\Agent\Agent;
use Larashed\Agent\AgentServiceProvider;
use Larashed\Agent\Http\Middlewares\RequestTrackerMiddleware;
use Larashed\Agent\Storage\StorageInterface;
use Larashed\Api\LarashedApi;
use Orchestra\Testbench\TestCase;

class AgentServiceProviderTest extends TestCase
{
    public function testServiceProviderDoesntBootIfLarashedIsDisabled()
    {
        app('config')->set('larashed.enabled', false);

        $agentStartCalled = false;

        $agent = \Mockery::mock(Agent::class);
        $agent->shouldReceive('start')->andReturnUsing(function () use (&$agentStartCalled) {
            $agentStartCalled = true;
        });

        app()->singleton(Agent::class, $agent);

        $sp = new AgentServiceProvider(app());
        $sp->boot();

        $this->assertFalse($agentStartCalled);
    }

    public function testServiceProviderBootsIfLarashedIsEnabled()
    {
        app('config')->set('larashed.enabled', true);

        $agentStartCalled = false;

        $agent = \Mockery::mock(Agent::class);
        $agent->shouldReceive('start')->andReturnUsing(function () use (&$agentStartCalled) {
            $agentStartCalled = true;
        });

        app()->singleton(Agent::class, function () use ($agent) {
            return $agent;
        });

        $sp = new AgentServiceProvider(app());
        $sp->boot();

        $this->assertTrue($agentStartCalled);
    }

    public function testServiceProviderDoesntRegisterIfLarashedIsDisabled()
    {
        app('config')->set('larashed.enabled', false);

        $sp = new AgentServiceProvider(app());
        $sp->register();

        $commands = array_keys(Artisan::all());

        $this->assertFalse(app()->has(StorageInterface::class));
        $this->assertFalse(app()->has(LarashedApi::class));
        $this->assertFalse(app()->has(Agent::class));
        $this->assertFalse(app()->has(RequestTrackerMiddleware::class));

        $this->assertFalse(in_array('larashed:daemon', $commands));
        $this->assertFalse(in_array('larashed:deploy', $commands));
        $this->assertFalse(in_array('larashed:server', $commands));
    }

    public function testServiceProviderRegistersIfLarashedIsEnabled()
    {
        app('config')->set('larashed.enabled', true);

        $sp = new AgentServiceProvider(app());
        $sp->register();

        $commands = array_keys(Artisan::all());

        $this->assertTrue(app()->has(StorageInterface::class));
        $this->assertTrue(app()->has(LarashedApi::class));
        $this->assertTrue(app()->has(Agent::class));
        $this->assertTrue(app()->has(RequestTrackerMiddleware::class));

        $this->assertTrue(in_array('larashed:daemon', $commands));
        $this->assertTrue(in_array('larashed:deploy', $commands));
        $this->assertTrue(in_array('larashed:server', $commands));
    }
}
