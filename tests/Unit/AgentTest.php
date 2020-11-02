<?php

namespace Larashed\Agent\Tests\Unit;

use Larashed\Agent\Agent;
use Larashed\Agent\Transport\TransportInterface;
use Larashed\Agent\Trackers\TrackerInterface;
use Orchestra\Testbench\TestCase;

class AgentTest extends TestCase
{
    public function testAgentStartTriggersAllTrackerBindings()
    {
        $agent = new Agent(\Mockery::mock(TransportInterface::class));

        $tracker1BindCalled = false;
        $tracker1 = $this->getTrackerMock(
            function () use (&$tracker1BindCalled) {
                $tracker1BindCalled = true;
            }
        );

        $tracker2BindCalled = false;
        $tracker2 = $this->getTrackerMock(
            function () use (&$tracker2BindCalled) {
                $tracker2BindCalled = true;
            }
        );

        $agent->addTracker('first', $tracker1);
        $agent->addTracker('second', $tracker2);

        $this->assertFalse($tracker1BindCalled);
        $this->assertFalse($tracker2BindCalled);

        $agent->start();

        $this->assertTrue($tracker1BindCalled);
        $this->assertTrue($tracker2BindCalled);
    }

    public function testAgentStopGathersDataFromAllTrackersAndStoresIt()
    {
        $storage = \Mockery::mock(TransportInterface::class);
        $storage->shouldReceive('push')->andReturnUsing(function ($data) use (&$collectedData) {
            $this->assertArrayHasKey('config', $data);
            unset($data['config']);

            $expected = [
                'first'  => ['tracker1'],
                'second' => ['tracker2'],
            ];

            $this->assertEquals($expected, $data);
        });

        $agent = new Agent($storage);

        $tracker1 = $this->getTrackerMock(
            null,
            function () {
                return ['tracker1'];
            }
        );

        $tracker2 = $this->getTrackerMock(
            null,
            function () {
                return ['tracker2'];
            }
        );

        $agent->addTracker('first', $tracker1);
        $agent->addTracker('second', $tracker2);

        $agent->stop();
    }

    public function testAgentStopCleansUpAllTrackers()
    {
        $agent = new Agent(\Mockery::mock(TransportInterface::class, ['push' => null]));

        $cleanCalled1 = false;
        $cleanCalled2 = false;

        $tracker1 = $this->getTrackerMock(
            null,
            null,
            function () use (&$cleanCalled1) {
                $cleanCalled1 = true;
            }
        );

        $tracker2 = $this->getTrackerMock(
            null,
            null,
            function () use (&$cleanCalled2) {
                $cleanCalled2 = true;
            }
        );

        $agent->addTracker('first', $tracker1);
        $agent->addTracker('second', $tracker2);

        $this->assertFalse($cleanCalled1);
        $this->assertFalse($cleanCalled2);

        $agent->stop();

        $this->assertTrue($cleanCalled1);
        $this->assertTrue($cleanCalled2);
    }

    protected function getTrackerMock($bind = null, $gather = null, $cleanup = null)
    {
        $mock = \Mockery::mock(TrackerInterface::class);

        $methods = ['bind' => $bind, 'gather' => $gather, 'cleanup' => $cleanup];

        foreach ($methods as $method => $return) {
            if ($return instanceof \Closure) {
                $mock->shouldReceive($method)->andReturnUsing($return);
            } else {
                $mock->shouldReceive($method)->andReturn($return);
            }
        }

        return $mock;
    }
}
