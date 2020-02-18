<?php

namespace Larashed\Agent\Tests\Unit\Database;

use Larashed\Agent\Tests\Traits\ConnectionMock;
use Larashed\Agent\Tests\Traits\MeasurementsMock;
use Orchestra\Testbench\TestCase;
use Illuminate\Database\Events\QueryExecuted;
use Larashed\Agent\Trackers\Database\Query;

class QueryTest extends TestCase
{
    use MeasurementsMock,
        ConnectionMock;

    protected $measurements;
    protected $queryExecuted;

    public function setUp()
    {
        parent::setUp();

        $this->measurements = $this->getMeasurementsMock('2018-01-01', 1, 1);
        $this->queryExecuted = new QueryExecuted('SELECT   * FROM table WHERE   1=1', [], 1, $this->getConnectionMock());
    }

    public function testQuerySerializesToArray()
    {
        $query = new Query($this->measurements, $this->queryExecuted);
        $result = $query->toArray();

        // freeze needed keys
        $this->assertArrayHasKey('query', $result);
        $this->assertArrayHasKey('processed_in', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('connection', $result);

        // test field values
        $expected = [
            'query'        => 'select * from table where 1=1',
            'created_at'   => '2018-01-01',
            'processed_in' => 1,
            'connection'   => 'database'
        ];

        $this->assertEquals($expected, $result);
    }
}
