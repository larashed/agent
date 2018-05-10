<?php

namespace Larashed\Agent\Tests\Traits;

use Larashed\Agent\System\Measurements;

trait MeasurementsMock
{
    public function getMeasurementsMock($time = null, $microtime = null, $memory = null, $microtimeDiff = null)
    {
        $mock = $this->createMock(Measurements::class);
        $mock->expects($this->any())
            ->method('time')
            ->will($this->returnValue($time));

        $mock->expects($this->any())
            ->method('microtime')
            ->will($this->returnValue($microtime));

        $mock->expects($this->any())
            ->method('memory')
            ->will($this->returnValue($memory));

        $mock->expects($this->any())
            ->method('microtimeDiff')
            ->will($this->returnValue($microtimeDiff));

        return $mock;
    }
}
