<?php

namespace Larashed\Agent\Tests\Console\Commands;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Larashed\Agent\Console\Commands\DaemonCommand;
use Larashed\Agent\Console\Sender;
use Larashed\Agent\Tests\TestConsoleKernel;
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

    public function testDaemonKeepsRunning()
    {
        $timesRan = 0;

        $sendCallback = function () use (&$timesRan) {
            $timesRan++;

            if ($timesRan > 2) {
                $this->command->setRunningMode(false);
            }

            return true;
        };

        $sender = \Mockery::mock(Sender::class);
        $sender->shouldReceive('send')->andReturnUsing($sendCallback);

        $this->command = new DaemonCommand($sender);

        app()->singleton(Kernel::class, TestConsoleKernel::class);
        app(Kernel::class)->registerCommand($this->command);

        Artisan::call('larashed:daemon', ['--sleep' => 0]);

        $this->assertEquals(3, $timesRan);
    }

    public function callDaemon($senderResult, $options = ['--single-run' => true])
    {
        $sender = \Mockery::mock(Sender::class);

        if (is_bool($senderResult)) {
            $sender->shouldReceive('send')->andReturn($senderResult);
        } else {
            $sender->shouldReceive('send')->andReturnUsing($senderResult);
        }

        $command = new DaemonCommand($sender);

        app()->singleton(Kernel::class, TestConsoleKernel::class);
        app(Kernel::class)->registerCommand($command);

        Artisan::call('larashed:daemon', $options);
    }
}
