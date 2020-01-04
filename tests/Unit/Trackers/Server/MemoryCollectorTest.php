<?php

namespace Larashed\Agent\Tests\Unit\Trackers\Server;

use Larashed\Agent\System\System;
use Larashed\Agent\Trackers\Server\MemoryCollector;
use Orchestra\Testbench\TestCase;

class MemoryCollectorTest extends TestCase
{
    /**
     * @var MemoryCollector
     */
    protected $memory;

    protected $kernelNewContent = '
        MemTotal:        8174812 kB
        MemFree:         1050248 kB
        MemAvailable:    4874268 kB
        Buffers:          114856 kB
        Cached:          3876724 kB
    ';

    protected $kernelOldContent = '
        MemTotal:        8174812 kB
        MemFree:         1050248 kB
        Buffers:          114856 kB
        Cached:          3876724 kB
    ';

    public function testFree()
    {
        $system = $this->getSystemMock($this->kernelNewContent);

        $this->memory = new MemoryCollector($system);

        $this->assertEquals(4874268, $this->memory->free());
    }

    public function testFreeOnOldKernel()
    {
        $system = $this->getSystemMock($this->kernelOldContent);

        $this->memory = new MemoryCollector($system);

        $this->assertEquals(5041828, $this->memory->free());
    }

    public function testTotal()
    {
        $system = $this->getSystemMock($this->kernelNewContent);

        $this->memory = new MemoryCollector($system);

        $this->assertEquals(8174812, $this->memory->total());
    }

    protected function getSystemMock($contents)
    {
        $mock = \Mockery::mock(System::class);
        $mock->shouldReceive('fileContents')->andReturn($contents);

        return $mock;
    }
}
