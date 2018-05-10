<?php

namespace Larashed\Agent\Tests\Console\Commands;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Larashed\Agent\Console\Commands\DeployCommand;
use Larashed\Agent\Console\Commands\ServerCommand;
use Larashed\Agent\System\System;
use Larashed\Agent\Tests\TestConsoleKernel;
use Larashed\Agent\Trackers\ServerEnvironmentTracker;
use Larashed\Api\Endpoints\Agent;
use Larashed\Agent\Api\LarashedApi;
use Orchestra\Testbench\TestCase;

class ServerCommandTest extends TestCase
{
    public function testServerCommandSendsData()
    {
        $endpoint = \Mockery::mock(Agent::class);
        $endpoint->shouldReceive('send')->andReturnUsing(function($dataToSend) {
            $this->assertArrayHasKey('server', $dataToSend);
            $this->assertArrayHasKey('data', $dataToSend['server']);
        });

        $api = \Mockery::mock(LarashedApi::class);
        $api->shouldReceive('agent')->andReturn($endpoint);

        $tracker = \Mockery::mock(ServerEnvironmentTracker::class);
        $tracker->shouldReceive('gather')->andReturn(['data' => []]);

        $command = new ServerCommand($tracker, $api);

        app()->singleton(Kernel::class, TestConsoleKernel::class);
        app(Kernel::class)->registerCommand($command);

        Artisan::call('larashed:server');

        $output = Artisan::output();

        $this->assertContains('Successfully sent collected server data.', $output);
    }

    public function testServerCommandFailsToSendsData()
    {
        $endpoint = \Mockery::mock(Agent::class);
        $endpoint->shouldReceive('send')->andThrows(new \Exception('error'));

        $api = \Mockery::mock(LarashedApi::class);
        $api->shouldReceive('agent')->andReturn($endpoint);

        $tracker = \Mockery::mock(ServerEnvironmentTracker::class);
        $tracker->shouldReceive('gather');

        $command = new ServerCommand($tracker, $api);

        app()->singleton(Kernel::class, TestConsoleKernel::class);
        app(Kernel::class)->registerCommand($command);

        Artisan::call('larashed:server');

        $output = Artisan::output();

        $this->assertContains('Failed to send collected server data.', $output);
    }
}
