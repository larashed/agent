<?php

namespace Larashed\Agent\Tests\Console\Commands;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Larashed\Agent\Console\Commands\ServerCommand;
use Larashed\Agent\Tests\TestConsoleKernel;
use Larashed\Agent\Trackers\ServerEnvironmentTracker;
use Larashed\Agent\Api\LarashedApi;
use Orchestra\Testbench\TestCase;

class ServerCommandTest extends TestCase
{
    public function testServerCommandSendsData()
    {
        $api = \Mockery::mock(LarashedApi::class);
        $api->shouldReceive('sendServerData')
            ->andReturn('');

        $tracker = \Mockery::mock(ServerEnvironmentTracker::class);
        $tracker->shouldReceive('gather')
                ->andReturn(['data' => []]);

        $command = new ServerCommand($tracker, $api);

        app()->singleton(Kernel::class, TestConsoleKernel::class);
        app(Kernel::class)->registerCommand($command);

        Artisan::call('larashed:server');

        $output = Artisan::output();

        $this->assertStringContainsStringIgnoringCase('Successfully sent collected server data.', $output);
    }

    public function testServerCommandFailsToSendsData()
    {
        $api = \Mockery::mock(LarashedApi::class);
        $api->shouldReceive('sendServerData')
            ->andThrows(new \Exception('error'));

        $tracker = \Mockery::mock(ServerEnvironmentTracker::class);
        $tracker->shouldReceive('gather');

        $command = new ServerCommand($tracker, $api);

        app()->singleton(Kernel::class, TestConsoleKernel::class);
        app(Kernel::class)->registerCommand($command);

        Artisan::call('larashed:server');

        $output = Artisan::output();

        $this->assertStringContainsStringIgnoringCase('Failed to send collected server data.', $output);
    }
}
