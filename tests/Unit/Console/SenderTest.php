<?php

namespace Larashed\Agent\Tests\Unit\Console;

use Larashed\Agent\Console\Sender;
use Larashed\Agent\Storage\StorageInterface;
use Larashed\Api\Endpoints\Agent;
use Larashed\Agent\Api\LarashedApi;
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

        $api = \Mockery::mock(LarashedApi::class);
        if (is_null($exception)) {
            $api->shouldReceive('sendAgentData')->andReturn($response);
        } else {
            $api->shouldReceive('sendAgentData')->andThrows($exception);
        }

        $sender = new Sender($storage, $api);

        return $sender;
    }
}
