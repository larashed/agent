<?php

namespace Larashed\Agent\Tests\Traits;

use Mockery;
use Illuminate\Database\Connection;

trait ConnectionMock
{
    protected function getConnectionMock($name = 'database')
    {
        $mock = Mockery::mock(Connection::class, [
            'getName' => $name
        ]);

        return $mock;
    }
}
