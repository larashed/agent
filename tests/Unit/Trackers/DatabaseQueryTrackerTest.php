<?php

namespace Larashed\Agent\Tests\Unit\Trackers;

use Orchestra\Testbench\TestCase;
use Larashed\Agent\Trackers\DatabaseQueryTracker;
use Larashed\Agent\Trackers\Database\QueryExcluder;
use Larashed\Agent\Trackers\Database\QueryExcluderConfig;
use Illuminate\Database\Events\QueryExecuted;
use Larashed\Agent\Tests\Traits\MeasurementsMock;
use Larashed\Agent\Tests\Traits\ConnectionMock;

class DatabaseQueryTrackerTest extends TestCase
{
    use MeasurementsMock,
        ConnectionMock;

    protected $measurements;
    protected $excluder;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->measurements = $this->getMeasurementsMock('2018-04-01T00:00:00.0000', 3006, 0);

        $this->excluder = new QueryExcluder(
            new QueryExcluderConfig('database', 'jobs', 'failed_jobs')
        );
    }

    public function testQueryTrackerBindsAndTracksEvents()
    {
        $tracker = new DatabaseQueryTracker($this->measurements, $this->excluder);
        $tracker->bind();

        event($this->basicQueryEventInstance());
        event($this->basicQueryEventInstance());

        $result = $tracker->gather();

        $this->assertCount(2, $result);
    }

    public function testQueryTrackerCleansUp()
    {
        $tracker = new DatabaseQueryTracker($this->measurements, $this->excluder);
        $tracker->bind();

        event($this->basicQueryEventInstance());

        $this->assertCount(1, $tracker->gather());

        $tracker->cleanup();

        $this->assertEmpty($tracker->gather());
    }

    public function testQueryTrackerExcludesQueries()
    {
        $tracker = new DatabaseQueryTracker($this->measurements, $this->excluder);
        $tracker->bind();

        event($this->excludedQueryEventInstance());

        $result = $tracker->gather();

        $this->assertEmpty($result);
    }

    protected function basicQueryEventInstance()
    {
        $sql = 'select * from `table` WHERE 1 = 1';
        $time = 0.32;

        return new QueryExecuted($sql, [], $time, $this->getConnectionMock());
    }

    protected function excludedQueryEventInstance($table = 'jobs')
    {
        $sql = 'select * from `' . $table . '` WHERE 1 = 1';
        $time = 0.32;

        return new QueryExecuted($sql, [], $time, $this->getConnectionMock());
    }
}
