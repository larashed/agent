<?php

namespace Larashed\Agent\Tests\Unit\Trackers\Server;

use Larashed\Agent\System\System;
use Larashed\Agent\Trackers\Server\DiskCollector;
use Orchestra\Testbench\TestCase;

class DiskCollectorTest extends TestCase
{
    /**
     * @var DiskCollector
     */
    protected $disk;

    public function setUp()
    {
        $system = $this->getSystemMock(1024.10, 2048.20);

        $this->disk = new DiskCollector($system);

        parent::setUp();
    }

    public function testFree()
    {
        $this->assertEquals(1024, $this->disk->free());
    }

    public function testTotal()
    {
        $this->assertEquals(2048, $this->disk->total());
    }

    protected function getSystemMock($free, $total)
    {
        $mock = \Mockery::mock(System::class);
        $mock->shouldReceive('freeDiskSpace')->andReturn($free);
        $mock->shouldReceive('totalDiskSpace')->andReturn($total);

        return $mock;
    }
}
