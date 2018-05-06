<?php

namespace Larashed\Agent\Tests\Traits;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request as BaseRequest;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;

trait RequestMock
{
    protected function getRequestMock($routeMock, $userMock, $url = '', $method = '', $referer = '', $ua = '', $ip = '')
    {
        $mock = $this->createMock(BaseRequest::class);
        $mock->expects($this->any())
            ->method('getUri')
            ->will($this->returnValue($url));

        $mock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue($method));

        $mock->expects($this->any())
            ->method('route')
            ->will($this->returnValue($routeMock));

        $mock->expects($this->any())
            ->method('user')
            ->will($this->returnValue($userMock));

        $mock->expects($this->any())
            ->method('header')
            ->withConsecutive(['referer'], ['user-agent'])
            ->willReturnOnConsecutiveCalls($referer, $ua);

        $mock->expects($this->any())
            ->method('getClientIp')
            ->will($this->returnValue($ip));

        return $mock;
    }

    protected function getRouteMock($uri = '', $name = '', $action = '')
    {
        $mock = $this->createMock(Route::class);
        $mock->expects($this->any())
            ->method('uri')
            ->will($this->returnValue($uri));

        $mock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        $mock->expects($this->any())
            ->method('getActionName')
            ->will($this->returnValue($action));

        return $mock;
    }

    protected function getUserMock($id = 0, $name = '')
    {
        $mock = $this->createMock(User::class);

        $mocked = $mock->expects($this->any())
            ->method('getAuthIdentifier')
            ->will($this->returnValue($id));

        $mocked->name = $name;
        $mock->name = $name;

        return $mock;
    }

    protected function getResponseMock($code = '', $exception = null)
    {
        $mock = $this->createMock(Response::class);
        $mock->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue($code));

        $mock->exception = $exception;

        return $mock;
    }
}
