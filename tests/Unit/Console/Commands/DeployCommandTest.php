<?php

namespace Larashed\Agent\Tests\Console\Commands;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Larashed\Agent\Console\Commands\DeployCommand;
use Larashed\Agent\System\System;
use Larashed\Agent\Tests\TestConsoleKernel;
use Orchestra\Testbench\TestCase;

class DeployCommandTest extends TestCase
{
    public function testDeployCommandFindsAndKillsProcess()
    {
        $exec = function () {
            return "1\n2\n3\n";
        };

        $execExcludedPid = function () {
            return "2\n";
        };

        $this->callDeploy($exec, $execExcludedPid);

        $output = Artisan::output();

        $this->assertContains('Killed Larashed process (1)', $output);
        $this->assertNotContains('Killed Larashed process (2)', $output);
        $this->assertContains('Killed Larashed process (3)', $output);
    }

    public function callDeploy(...$exec)
    {
        $system = \Mockery::mock(System::class);
        $system->shouldReceive('exec')->andReturnUsing(...$exec);

        $command = new DeployCommand($system);

        app()->singleton(Kernel::class, TestConsoleKernel::class);
        app(Kernel::class)->registerCommand($command);

        Artisan::call('larashed:deploy');
    }
}
