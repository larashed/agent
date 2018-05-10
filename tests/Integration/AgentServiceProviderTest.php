<?php

namespace Larashed\Agent\Tests\Integration;

use Illuminate\Support\Facades\Artisan;
use Larashed\Agent\Agent;
use Larashed\Agent\AgentServiceProvider;
use Larashed\Agent\Http\Middlewares\RequestTrackerMiddleware;
use Larashed\Agent\Storage\StorageInterface;
use Larashed\Agent\Tests\Helpers\LaravelVersion;
use Larashed\Agent\Api\LarashedApi;
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

        $this->app->singleton(Agent::class, $agent);

        $sp = new AgentServiceProvider($this->app);
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

        $this->app->singleton(Agent::class, function () use ($agent) {
            return $agent;
        });

        $sp = new AgentServiceProvider($this->app);
        $sp->boot();

        $this->assertTrue($agentStartCalled);
    }

    public function testServiceProviderDoesntRegisterIfLarashedIsDisabled()
    {
        app('config')->set('larashed.enabled', false);

        $sp = new AgentServiceProvider($this->app);
        $sp->register();

        $commands = array_keys(Artisan::all());

        $this->assertFalse($this->containerHas(StorageInterface::class));
        $this->assertFalse($this->containerHas(LarashedApi::class));
        $this->assertFalse($this->containerHas(Agent::class));
        $this->assertFalse($this->containerHas(RequestTrackerMiddleware::class));

        $this->assertFalse(in_array('larashed:daemon', $commands));
        $this->assertFalse(in_array('larashed:deploy', $commands));
        $this->assertFalse(in_array('larashed:server', $commands));
    }

    public function testServiceProviderRegistersIfLarashedIsEnabled()
    {
        app('config')->set('larashed.enabled', true);

        $sp = new AgentServiceProvider($this->app);
        $sp->register();

        $commands = array_keys(Artisan::all());

        $this->assertTrue($this->containerHas(StorageInterface::class));
        $this->assertTrue($this->containerHas(LarashedApi::class));
        $this->assertTrue($this->containerHas(Agent::class));
        $this->assertTrue($this->containerHas(RequestTrackerMiddleware::class));

        $this->assertTrue(in_array('larashed:daemon', $commands));
        $this->assertTrue(in_array('larashed:deploy', $commands));
        $this->assertTrue(in_array('larashed:server', $commands));
    }

    protected function containerHas($class)
    {
        return $this->app->bound($class);
    }
}
