<?php

namespace Larashed\Agent\Tests\Unit\Trackers;

use Illuminate\Contracts\Foundation\Application;
use Larashed\Agent\Tests\Traits\RequestMock;
use Larashed\Agent\Trackers\Server\CPUUsageCollector;
use Larashed\Agent\Trackers\Server\DiskCollector;
use Larashed\Agent\Trackers\Server\LaravelEnvironmentCollector;
use Larashed\Agent\Trackers\Server\LoadAverageCollector;
use Larashed\Agent\Trackers\Server\MemoryCollector;
use Larashed\Agent\Trackers\Server\ServiceCollector;
use Larashed\Agent\Trackers\Server\SystemEnvironmentCollector;
use Larashed\Agent\Trackers\ApplicationEnvironmentTracker;
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

        $tracker = $this->getApplicationEnvironmentTrackerInstance(false);
        $tracker->bind();
    }

    public function testGatherReturnsRequiredStructure()
    {
        $tracker = $this->getApplicationEnvironmentTrackerInstance();
        $tracker->bind();

        $data = $tracker->gather();

        $this->assertArrayHasKey('created_at', $data);

        $this->assertArrayHasKey('app', $data);
        $this->assertArrayHasKey('name', $data['app']);
        $this->assertArrayHasKey('url', $data['app']);
        $this->assertArrayHasKey('drivers', $data['app']);
        $this->assertArrayHasKey('laravel_version', $data['app']);

        $this->assertArrayHasKey('queue', $data['app']['drivers']);
        $this->assertArrayHasKey('database', $data['app']['drivers']);
        $this->assertArrayHasKey('cache', $data['app']['drivers']);
        $this->assertArrayHasKey('mail', $data['app']['drivers']);
    }

    protected function getApplicationEnvironmentTrackerInstance($cli = true)
    {
        $app = \Mockery::mock(Application::class);
        $app->shouldReceive('runningInConsole')->andReturn($cli);

        $laravel = \Mockery::mock(LaravelEnvironmentCollector::class);
        $laravel->shouldReceive('appName')->andReturn('');
        $laravel->shouldReceive('environment')->andReturn('');
        $laravel->shouldReceive('url')->andReturn('');

        $laravel->shouldReceive('queueDriver')->andReturn('');
        $laravel->shouldReceive('databaseDriver')->andReturn('');
        $laravel->shouldReceive('cacheDriver')->andReturn('');
        $laravel->shouldReceive('mailDriver')->andReturn('');
        $laravel->shouldReceive('laravelVersion')->andReturn('');

        $tracker = new ApplicationEnvironmentTracker(
            $app,
            $this->getMeasurementsMock(),
            $laravel
        );

        return $tracker;
    }
}
