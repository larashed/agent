<?php

namespace Larashed\Agent\Tests\Unit\Trackers\Database;

use Orchestra\Testbench\TestCase;
use Larashed\Agent\Trackers\Database\QueryExcluderConfig;

class QueryExcluderConfigTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('queue.default', 'database');
        $app['config']->set('queue.connections.database.table', 'jobs');
        $app['config']->set('queue.failed.table', 'failed_jobs');
    }

    public function testBuildsFromConfig()
    {
        $config = QueryExcluderConfig::fromConfig();
        $this->assertEquals('database', $config->getQueueDriver());
        $this->assertEquals('jobs', $config->getJobTable());
        $this->assertEquals('failed_jobs', $config->getFailedJobTable());

        $this->assertInstanceOf(QueryExcluderConfig::class, $config);
    }
}
