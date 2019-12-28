<?php

namespace Larashed\Agent\Tests\Unit\Trackers\Server;

use Larashed\Agent\System\System;
use Larashed\Agent\Trackers\Server\MemoryCollector;
use Larashed\Agent\Trackers\Server\ServiceCollector;
use Orchestra\Testbench\TestCase;

class ServiceCollectorTest extends TestCase
{
    public function testOnlyActiveServicesAreCollected()
    {
        $input = '
         [ + ]  service1
         [ - ]  service2
         [ + ]  service3
         [ + ]  service4
         [ - ]  service5
         [ ? ]  service6
         [ + ]  service7
         badservice
         ';

        $expected = [
            [
                'status' => ServiceCollector::STATUS_RUNNING,
                'name'   => 'service1',
            ],
            [
                'status' => ServiceCollector::STATUS_STOPPED,
                'name'   => 'service2',
            ],
            [
                'status' => ServiceCollector::STATUS_RUNNING,
                'name'   => 'service3',
            ],
            [
                'status' => ServiceCollector::STATUS_RUNNING,
                'name'   => 'service4',
            ],
            [
                'status' => ServiceCollector::STATUS_STOPPED,
                'name'   => 'service5',
            ],
            [
                'status' => ServiceCollector::STATUS_UNDETERMINED,
                'name'   => 'service6',
            ],
            [
                'status' => ServiceCollector::STATUS_RUNNING,
                'name'   => 'service7',
            ]
        ];

        $services = $this->getServiceCollector($input);

        $this->assertEquals($expected, $services->services());
    }

    protected function getServiceCollector($input)
    {
        $system = \Mockery::mock(System::class);
        $system->shouldReceive('exec')->andReturn($input);

        $services = new ServiceCollector($system);

        return $services;
    }
}
