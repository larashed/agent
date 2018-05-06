<?php

namespace Larashed\Agent\Tests\Unit\Trackers\Server;

use Illuminate\Foundation\Application;
use Larashed\Agent\Trackers\Server\LaravelEnvironmentCollector;
use Orchestra\Testbench\TestCase;

class LaravelEnvironmentCollectorTest extends TestCase
{
    /**
     * @var LaravelEnvironmentCollector
     */
    protected $laravel;

    public function setUp()
    {
        $app = \Mockery::mock(Application::class);
        $app->shouldReceive('version')->andReturn('5.6.40');

        $this->laravel = new LaravelEnvironmentCollector($app);

        parent::setUp();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.name', 'larashed');
        $app['config']->set('app.env', 'local');
        $app['config']->set('app.url', 'http://local.app');

        $app['config']->set('queue.default', 'redis');
        $app['config']->set('database.default', 'pgsql');
        $app['config']->set('cache.default', 'redis');
        $app['config']->set('mail.driver', 'sendmail');
    }

    public function testAppName()
    {
        $this->assertEquals('larashed', $this->laravel->appName());
    }

    public function testEnvironment()
    {
        $this->assertEquals('local', $this->laravel->environment());
    }

    public function testUrl()
    {
        $this->assertEquals('http://local.app', $this->laravel->url());
    }

    public function testLaravelVersion()
    {
        $this->assertEquals('5.6.40', $this->laravel->laravelVersion());
    }

    public function testQueueDriver()
    {
        $this->assertEquals('redis', $this->laravel->queueDriver());
    }

    public function testDatabaseDriver()
    {
        $this->assertEquals('pgsql', $this->laravel->databaseDriver());
    }

    public function testCacheDriver()
    {
        $this->assertEquals('redis', $this->laravel->cacheDriver());
    }

    public function testMailDriver()
    {
        $this->assertEquals('sendmail', $this->laravel->mailDriver());
    }
}
