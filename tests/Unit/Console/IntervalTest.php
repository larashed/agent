<?php

namespace Larashed\Agent\Tests\Unit\Console;

use Larashed\Agent\Console\Interval;

class IntervalTest extends \PHPUnit\Framework\TestCase
{
    public function testSendingDataSucceeds()
    {
        $interval = new Interval(0.000100);
        $interval->start();

        usleep(200);

        $this->assertTrue($interval->passed());
        $interval->restart();
        $this->assertFalse($interval->passed());
    }
}
