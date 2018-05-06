<?php

namespace Larashed\Agent\Tests\Unit\Console;

use Larashed\Agent\Console\Sender;
use Larashed\Agent\Storage\StorageInterface;
use Larashed\Api\Endpoints\Agent;
use Larashed\Api\LarashedApi;
use Orchestra\Testbench\TestCase;

class SenderTest extends TestCase
{
    public function testSendingDataSucceeds()
    {
        $sender = $this->getSenderInstance(['record'], ['success' => true]);
        $result = $sender->send(1);

        $this->assertTrue($result);
    }

    public function testSendingEmptyDatasetSucceeds()
    {
        $sender = $this->getSenderInstance([], ['success' => true]);
        $result = $sender->send(1);

        $this->assertTrue($result);
    }

    public function testSendingDataFails()
    {
        $sender = $this->getSenderInstance(['record'], ['success' => false]);
        $result = $sender->send(1);

        $this->assertFalse($result);
    }

    public function testApiReturningInvalidResponseFails()
    {
        $sender = $this->getSenderInstance(['record'], null);
        $result = $sender->send(1);

        $this->assertFalse($result);
    }

    public function testCatchingExceptionReturnsFalse()
    {
        $sender = $this->getSenderInstance(['record'], ['success' => true], new \Exception('asd'));
        $result = $sender->send(1);

        $this->assertFalse($result);
    }

    protected function getSenderInstance($records = [], $response = [], $exception = null)
    {
        $storage = \Mockery::mock(StorageInterface::class);
        $storage->shouldReceive('records')->andReturn(collect($records));
        $storage->shouldReceive('remove')->andReturn();

        $endpoint = \Mockery::mock(Agent::class);
        if (is_null($exception)) {
            $endpoint->shouldReceive('send')->andReturn($response);
        } else {
            $endpoint->shouldReceive('send')->andThrows($exception);
        }

        $api = \Mockery::mock(LarashedApi::class);
        $api->shouldReceive('agent')->andReturn($endpoint);

        $sender = new Sender($storage, $api);

        return $sender;
    }
}
