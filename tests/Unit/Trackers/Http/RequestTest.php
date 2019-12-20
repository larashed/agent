<?php

namespace Larashed\Agent\Tests\Unit\Trackers\Http;

use Orchestra\Testbench\TestCase;
use Larashed\Agent\Tests\Traits\MeasurementsMock;
use Larashed\Agent\Tests\Traits\RequestMock;
use Larashed\Agent\Trackers\Http\Request;

class RequestTest extends TestCase
{
    use MeasurementsMock, RequestMock;

    public function setUp(): void
    {
        if(!defined('LARAVEL_START')) {
            define('LARAVEL_START', 0);
        }

        $this->url = 'http://laravel.app/dashboard/?date=now';
        $this->method = 'GET';

        $this->routeUri = 'dashboard';
        $this->routeName = 'app.dashboard';
        $this->routeAction = 'App\Http\Controllers\DashboardController@index';

        $this->userId = 1;
        $this->userName = null;

        $this->referer = 'http://laravel.app/login';
        $this->ua = 'chrome-ua';
        $this->ip = '127.0.0.1';

        $this->processedIn = 0.1;
        $this->time = '2018-01-01T00:00:00.0000';
        $this->measurements = $this->getMeasurementsMock($this->time, 0, 0, $this->processedIn);

        parent::setUp();
    }

    public function testRequestToArrayReturnsWithKeys()
    {
        $request = new Request($this->measurements, $this->getBaseRequestMock());
        $result = $request->toArray();

        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('processed_in', $result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('method', $result);

        $this->assertArrayHasKey('route', $result);
        $this->assertArrayHasKey('uri', $result['route']);
        $this->assertArrayHasKey('name', $result['route']);
        $this->assertArrayHasKey('action', $result['route']);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('id', $result['user']);
        $this->assertArrayHasKey('name', $result['user']);

        $this->assertArrayHasKey('meta', $result);
        $this->assertArrayHasKey('referrer', $result['meta']);
        $this->assertArrayHasKey('user-agent', $result['meta']);
        $this->assertArrayHasKey('ip', $result['meta']);
    }

    public function testRequestToArrayReturnsWithValues()
    {
        $request = new Request($this->measurements, $this->getBaseRequestMock());
        $result = $request->toArray();

        $this->assertEquals($this->time, $result['created_at']);
        $this->assertEquals($this->processedIn, $result['processed_in']);
        $this->assertEquals($this->url, $result['url']);
        $this->assertEquals($this->method, $result['method']);

        $this->assertEquals($this->routeUri, $result['route']['uri']);
        $this->assertEquals($this->routeName, $result['route']['name']);
        $this->assertEquals($this->routeAction, $result['route']['action']);

        $this->assertEquals($this->userId, $result['user']['id']);
        $this->assertEquals($this->userName, null);
        //$this->assertEquals($this->userName, $result['user']['name']);

        $this->assertEquals($this->referer, $result['meta']['referrer']);
        $this->assertEquals($this->ua, $result['meta']['user-agent']);
        $this->assertEquals($this->ip, $result['meta']['ip']);
    }

    protected function getBaseRequestMock()
    {
        $mock = $this->getRequestMock(
            $this->getRouteMock($this->routeUri, $this->routeName, $this->routeAction),
            $this->getUserMock($this->userId, $this->userName),
            $this->url,
            $this->method,
            $this->referer,
            $this->ua,
            $this->ip
        );

        return $mock;
    }
}
