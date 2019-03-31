<?php

namespace Larashed\Agent\Tests\Console\Commands;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Larashed\Agent\Api\LarashedApi;
use Larashed\Agent\Console\Commands\DeployCommand;
use Larashed\Agent\Console\DaemonRestartHandler;
use Larashed\Agent\System\Measurements;
use Larashed\Agent\System\System;
use Larashed\Agent\Tests\TestConsoleKernel;
use Orchestra\Testbench\TestCase;

class DeployCommandTest extends TestCase
{
    public $markedForRestart;
    public $git;

    public function testDeployCommandFinishes()
    {
        $this->callDeploy();

        $output = Artisan::output();

        $this->assertEquals('', $output);
        $this->assertTrue($this->markedForRestart);
    }

    public function callDeploy(...$exec)
    {
        $system = \Mockery::mock(System::class);
        $system->shouldReceive('exec')->andReturnUsing(...$exec);

        $restart = \Mockery::mock(DaemonRestartHandler::class);
        $restart->shouldReceive('markNeeded')->andReturnUsing(function () {
            $this->markedForRestart = true;
        });

        $api = \Mockery::mock(LarashedApi::class);
        $api->shouldReceive('sendDeploymentData')->andReturn();
        $command = new DeployCommand($system, new Measurements(), $restart, $api);

        app()->singleton(Kernel::class, TestConsoleKernel::class);
        app(Kernel::class)->registerCommand($command);

        Artisan::call('larashed:deploy');
    }
}
