<?php

namespace Larashed\Agent\Tests\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Larashed\Agent\Api\LarashedApi;
use Larashed\Agent\Console\Commands\DaemonCommand;
use Larashed\Agent\Console\Commands\ServerCommand;
use Larashed\Agent\Console\DaemonRestartHandler;
use Larashed\Agent\Console\Interval;
use Larashed\Agent\Console\Sender;
use Larashed\Agent\Tests\TestConsoleKernel;
use Larashed\Agent\Trackers\ServerEnvironmentTracker;
use Orchestra\Testbench\TestCase;

class DaemonCommandTest extends TestCase
{
    public function testDaemonSingleRunReportsOnSuccess()
    {
        $this->callDaemon(true);

        $output = Artisan::output();

        $this->assertContains('Successfully sent collected data.', $output);
    }

    public function testDaemonSingleRunReportsOnFailure()
    {
        $this->callDaemon(false);

        $output = Artisan::output();

        $this->assertContains('Failed to send collected data.', $output);
    }

    public function testDaemonUsesCorrectLimit()
    {
        // use string to check casting
        $limit = '100';

        $senderReceivedLimit = null;

        $sender = function ($passedLimit) use (&$senderReceivedLimit) {
            $senderReceivedLimit = $passedLimit;
        };

        $this->callDaemon($sender, ['--single-run' => true, '--limit' => $limit]);

        $this->assertEquals(100, $senderReceivedLimit);
    }

    public function testDaemonKeepsRunningAndCallsServerCommand()
    {
        $timesRan = 0;

        $sender = \Mockery::mock(Sender::class);
        $sender->shouldReceive('send')->andReturnUsing(function () use (&$timesRan) {
            $timesRan++;

            if ($timesRan > 2) {
                $this->command->setRunningMode(false);
            }

            return true;
        });

        $interval = new Interval(0.000001);
        $restart = new DaemonRestartHandler(app(Filesystem::class), '/tmp/123');

        $this->command = new DaemonCommand($sender, $interval, $restart);

        $serverCommand = new \Larashed\Agent\Tests\Unit\Mocks\ServerCommand();

        app()->singleton(Kernel::class, TestConsoleKernel::class);
        app(Kernel::class)->registerCommand($this->command);
        app(Kernel::class)->registerCommand($serverCommand);

        Artisan::call('larashed:daemon', ['--sleep' => 0]);

        $this->assertEquals(3, $timesRan);
        $this->assertEquals(3, $serverCommand->calledTimes);
    }

    protected function callDaemon($senderResult, $options = ['--single-run' => true])
    {
        $sender = \Mockery::mock(Sender::class);

        if (is_bool($senderResult)) {
            $sender->shouldReceive('send')->andReturn($senderResult);
        } else {
            $sender->shouldReceive('send')->andReturnUsing($senderResult);
        }

        $interval = new Interval(0);
        $restart = new DaemonRestartHandler(app(Filesystem::class), '/tmp/123');
        $command = new DaemonCommand($sender, $interval, $restart);

        app()->singleton(Kernel::class, TestConsoleKernel::class);
        app(Kernel::class)->registerCommand($command);

        Artisan::call('larashed:daemon', $options);
    }
}
