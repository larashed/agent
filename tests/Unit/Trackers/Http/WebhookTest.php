<?php

namespace Larashed\Agent\Tests\Unit\Trackers\Http;

use Larashed\Agent\Tests\Traits\WebhookRequestMock;
use Larashed\Agent\Trackers\Http\Webhook;
use Orchestra\Testbench\TestCase;
use Larashed\Agent\Tests\Traits\MeasurementsMock;

class WebhookTest extends TestCase
{
    use MeasurementsMock, WebhookRequestMock;

    public function testWebhookToArrayHasCorrectStructure()
    {
        $webhook = new Webhook($this->getMeasurementsMock(), $this->getWebhookRequestMock(), '', '');
        $data = $webhook->toArray();

        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('source', $data);
        $this->assertArrayHasKey('payload', $data);
    }

    public function testWebhookToArrayReturnsCorrectValues()
    {
        $webhook = new Webhook(
            $this->getMeasurementsMock(),
            $this->getWebhookRequestMock(),
            'some-source',
            'some-name'
        );

        $data = $webhook->toArray();

        $this->assertEquals('some-name', $data['name']);
        $this->assertEquals('some-source', $data['source']);

        $payload = [
            'url'     => 'http://webhook',
            'method'  => 'post',
            'body'    => 'Y29udGVudA==', //'content'
            'headers' => [
                'user-agent' => 'agent',
                'ip'         => '127.0.0.1'
            ]
        ];

        $this->assertArraySubset($payload, $data['payload']);
    }

    public function testWebhookToArraySkipsCookies()
    {
        $webhook = new Webhook(
            $this->getMeasurementsMock(),
            $this->getWebhookRequestMock(['one-cookie', 'two-cookie']),
            'some-source',
            'some-name'
        );

        $data = $webhook->toArray();

        $this->assertArrayNotHasKey('cookie', $data['payload']['headers']);
    }
}
