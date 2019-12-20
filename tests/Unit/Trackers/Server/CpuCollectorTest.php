<?php

namespace Larashed\Agent\Tests\Unit\Trackers\Server;

use Larashed\Agent\System\System;
use Larashed\Agent\Trackers\Server\CpuCollector;
use Larashed\Agent\Trackers\Server\DiskCollector;
use Orchestra\Testbench\TestCase;

class CpuCollectorTest extends TestCase
{
    /**
     * @var CpuCollector
     */
    protected $cpu;

    public function setUp(): void
    {
        $first = 'cpu  180594930 12962 51433614 1364705275 242872 0 1112522 182457 0 0';
        $second = 'cpu  180595237 12962 51433812 1364706969 242872 0 1112523 182458 0 0';

        $system = \Mockery::mock(System::class);
        $system->shouldReceive('fileContents')
            ->andReturn($first, $second);

        $this->cpu = new CpuCollector($system);

        parent::setUp();
    }

    public function testCpu()
    {
        $this->assertEquals(23.53, $this->cpu->cpu());
    }
}
