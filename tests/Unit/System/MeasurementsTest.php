<?php

namespace Larashed\Agent\Tests\Unit\System;

use Carbon\Carbon;
use Larashed\Agent\System\Measurements;
use Orchestra\Testbench\TestCase;

class MeasurementsTest extends TestCase
{
    public function testDatetimeWithDelay()
    {
        $date = "2021-04-19T17:49:45.000000+00:00";
        $unix = 1618854585;
        $m = new Measurements();

        $result = $m->datetimeWithDelay(Carbon::parse($date), 0);
        $this->assertEquals($date, $result);

        $result = $m->datetimeWithDelay(Carbon::parse($date), $unix + 60);
        $this->assertEquals("2021-04-19T17:50:45.000000+00:00", $result);
    }
}
