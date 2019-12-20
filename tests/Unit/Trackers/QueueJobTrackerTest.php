<?php

namespace Larashed\Agent\Tests\Unit\Trackers;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Larashed\Agent\Agent;
use Larashed\Agent\Tests\Traits\RequestMock;
use Larashed\Agent\Trackers\QueueJobTracker;
use Orchestra\Testbench\TestCase;
use Larashed\Agent\Tests\Traits\MeasurementsMock;

class QueueJobTrackerTest extends TestCase
{
    use MeasurementsMock, RequestMock;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testJobTrackerBindsAndTracksJobStartEvent()
    {
        $tracker = new QueueJobTracker($this->getAgentMock(), $this->getMeasurementsMock());
        $tracker->bind();

        event($this->getJobProcessingMock());

        $this->assertNotEmpty($tracker->gather());
    }

    public function testJobTrackerBindsAndTracksJobFinishedEvent()
    {
        $tracker = new QueueJobTracker($this->getAgentMock(), $this->getMeasurementsMock());
        $tracker->bind();

        event($this->getJobProcessingMock());
        event($this->getJobProcessedMock());

        $job = $tracker->gather();

        $this->assertNotEmpty($job);
        $this->assertEquals('processed', $job['connection']);
        $this->assertEquals('default', $job['queue']);
    }

    public function testJobTrackerBindsAndTracksJobFailedEvent()
    {
        $tracker = new QueueJobTracker($this->getAgentMock(), $this->getMeasurementsMock());
        $tracker->bind();

        event($this->getJobProcessingMock());
        event($this->getJobFailedMock());

        $job = $tracker->gather();

        $this->assertNotEmpty($job);
        $this->assertEquals('processed', $job['connection']);
        $this->assertEquals('default', $job['queue']);

        $this->assertNotEmpty($job['exception']);
        $this->assertEquals('message', $job['exception'][0]['message']);
    }

    public function testJobTrackerCleansItself()
    {
        $tracker = new QueueJobTracker($this->getAgentMock(), $this->getMeasurementsMock());
        $tracker->bind();

        event($this->getJobProcessingMock());

        $this->assertNotEmpty($tracker->gather());

        $tracker->cleanup();

        $this->assertEmpty($tracker->gather());
    }

    protected function getAgentMock()
    {
        $mock = \Mockery::mock(Agent::class, [
            'stop' => null
        ]);

        return $mock;
    }

    protected function getJobProcessingMock()
    {
        $mock = new JobProcessing('processing', $this->getJobMock());

        return $mock;
    }

    protected function getJobProcessedMock()
    {
        $mock = new JobProcessed('processed', $this->getJobMock());

        return $mock;
    }

    protected function getJobFailedMock()
    {
        $mock = new JobFailed('processed', $this->getJobMock(), new \Exception('message'));

        return $mock;
    }

    protected function getJobMock()
    {
        $job = \Mockery::mock(Job::class);
        $job->shouldReceive('resolveName')->andReturn('Job');
        $job->shouldReceive('attempts')->andReturn(3);
        $job->shouldReceive('getQueue')->andReturn('default');

        return $job;
    }
}
