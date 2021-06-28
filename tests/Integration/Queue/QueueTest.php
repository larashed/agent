<?php

namespace Larashed\Agent\Tests\Integration\Queue;

use Orchestra\Testbench\TestCase;

class QueueTest extends TestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver'   => 'mysql',
            'port'     => '3306',
            'host'     => 'db',
            'database' => 'agent',
            'username' => 'agent',
            'password' => 'agent_secret'
        ]);
        $app['config']->set('database.redis.default', [
            'host'     => 'redis',
            'port'     => '6379',
            'database' => '0',
            'password' => null
        ]);

        $app['config']->set('queue.default', 'redis');
    }

    public function testHealthCheckRouteExists()
    {

    }

}
