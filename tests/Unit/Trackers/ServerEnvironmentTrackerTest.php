<?php

namespace Larashed\Agent\Tests\Unit\Trackers;

use Illuminate\Contracts\Foundation\Application;
use Larashed\Agent\Tests\Traits\RequestMock;
use Larashed\Agent\Trackers\Server\CpuCollector;
use Larashed\Agent\Trackers\Server\DiskCollector;
use Larashed\Agent\Trackers\Server\LaravelEnvironmentCollector;
use Larashed\Agent\Trackers\Server\MemoryCollector;
use Larashed\Agent\Trackers\Server\ServiceCollector;
use Larashed\Agent\Trackers\Server\SystemEnvironmentCollector;
use Larashed\Agent\Trackers\ServerEnvironmentTracker;
use Orchestra\Testbench\TestCase;
use Larashed\Agent\Tests\Traits\MeasurementsMock;

class ServerEnvironmentTrackerTest extends TestCase
{
    use MeasurementsMock, RequestMock;

    public function setUp()
    {
        parent::setUp();
    }

    public function testBindFailsWhenCalledFromNonCliContext()
    {
        $this->expectException(\RuntimeException::class);

        $tracker = $this->getServerEnvironmentTrackerInstance(false);
        $tracker->bind();
    }

    public function testGatherReturnsRequiredStructure()
    {
        $tracker = $this->getServerEnvironmentTrackerInstance();
        $tracker->bind();

        $data = $tracker->gather();

        $this->assertArrayHasKey('created_at', $data);

        $this->assertArrayHasKey('app', $data);
        $this->assertArrayHasKey('name', $data['app']);
        $this->assertArrayHasKey('env', $data['app']);
        $this->assertArrayHasKey('url', $data['app']);
        $this->assertArrayHasKey('drivers', $data['app']);
        $this->assertArrayHasKey('laravel_version', $data['app']);

        $this->assertArrayHasKey('queue', $data['app']['drivers']);
        $this->assertArrayHasKey('database', $data['app']['drivers']);
        $this->assertArrayHasKey('cache', $data['app']['drivers']);
        $this->assertArrayHasKey('mail', $data['app']['drivers']);

        $this->assertArrayHasKey('system', $data);
        $this->assertArrayHasKey('reboot_required', $data['system']);
        $this->assertArrayHasKey('php_version', $data['system']);
        $this->assertArrayHasKey('hostname', $data['system']);
        $this->assertArrayHasKey('uptime', $data['system']);
        $this->assertArrayHasKey('os', $data['system']);

        $this->assertArrayHasKey('id', $data['system']['os']);
        $this->assertArrayHasKey('name', $data['system']['os']);
        $this->assertArrayHasKey('pretty_name', $data['system']['os']);
        $this->assertArrayHasKey('version', $data['system']['os']);

        $this->assertArrayHasKey('services', $data['system']);

        $this->assertArrayHasKey('resources', $data);
        $this->assertArrayHasKey('cpu', $data['resources']);
        $this->assertArrayHasKey('memory_free', $data['resources']);
        $this->assertArrayHasKey('memory_total', $data['resources']);
        $this->assertArrayHasKey('disk_total', $data['resources']);
        $this->assertArrayHasKey('disk_free', $data['resources']);
    }

    protected function getServerEnvironmentTrackerInstance($cli = true)
    {
        $app = \Mockery::mock(Application::class);
        $app->shouldReceive('runningInConsole')->andReturn($cli);

        $service = \Mockery::mock(ServiceCollector::class);
        $service->shouldReceive('services')->andReturn(['service1']);

        $memory = \Mockery::mock(MemoryCollector::class);
        $memory->shouldReceive('free')->andReturn(1024);
        $memory->shouldReceive('total')->andReturn(2048);

        $disk = \Mockery::mock(DiskCollector::class);
        $disk->shouldReceive('free')->andReturn(1);
        $disk->shouldReceive('total')->andReturn(2);

        $cpu = \Mockery::mock(CpuCollector::class);
        $cpu->shouldReceive('cpu')->andReturn(1);

        $laravel = \Mockery::mock(LaravelEnvironmentCollector::class);
        $laravel->shouldReceive('appName')->andReturn('');
        $laravel->shouldReceive('environment')->andReturn('');
        $laravel->shouldReceive('url')->andReturn('');

        $laravel->shouldReceive('queueDriver')->andReturn('');
        $laravel->shouldReceive('databaseDriver')->andReturn('');
        $laravel->shouldReceive('cacheDriver')->andReturn('');
        $laravel->shouldReceive('mailDriver')->andReturn('');
        $laravel->shouldReceive('laravelVersion')->andReturn('');

        $system = \Mockery::mock(SystemEnvironmentCollector::class);
        $system->shouldReceive('rebootRequired')->andReturn(false);
        $system->shouldReceive('phpVersion')->andReturn('');
        $system->shouldReceive('hostname')->andReturn('');
        $system->shouldReceive('uptime')->andReturn('uptime');
        $system->shouldReceive('osInformation')->andReturn(['id' => '', 'name' => '', 'pretty_name' => '', 'version' => '']);

        $tracker = new ServerEnvironmentTracker(
            $app,
            $this->getMeasurementsMock(),
            $service,
            $memory,
            $disk,
            $cpu,
            $laravel,
            $system
        );

        return $tracker;
    }
}
