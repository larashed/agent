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

    public function setUp()
    {
        $contents = '
        MemTotal:        2046652 kB
        MemFree:          101808 kB';

        $system = $this->getSystemMock($contents);

        $this->memory = new MemoryCollector($system);

        parent::setUp();
    }

    public function testFree()
    {
        $this->assertEquals(101808, $this->memory->free());
    }

    public function testTotal()
    {
        $this->assertEquals(2046652, $this->memory->total());
    }

    public function testMemoryCollectorFails()
    {
        $system = $this->getSystemMock('');

        $collector = new MemoryCollector($system);

        $this->assertNull($collector->total());
        $this->assertNull($collector->free());
    }

    protected function getSystemMock($contents)
    {
        $mock = \Mockery::mock(System::class);
        $mock->shouldReceive('fileContents')->andReturn($contents);

        return $mock;
    }
}
