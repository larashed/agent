<?php

namespace Larashed\Agent\Tests\Traits;

use Illuminate\Http\Request;

trait WebhookRequestMock
{
    public function getWebhookRequestMock(array $cookies = null)
    {
        $headers = collect([
            'user-agent' => ['agent'],
            'ip' => ['127.0.0.1'],
        ]);

        if (!is_null($cookies)) {
            $headers->put('cookie', [$cookies]);
        }

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('fullUrl')->andReturn('http://webhook');
        $request->shouldReceive('getMethod')->andReturn('post');
        $request->shouldReceive('getContent')->andReturn('content');
        $request->shouldReceive('header')->andReturn($headers);

        return $request;
    }
}
