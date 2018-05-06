<?php

namespace Larashed\Agent\Tests\Unit\Http\Controllers;

use Larashed\Agent\Http\Controllers\HealthCheckController;
use Orchestra\Testbench\TestCase;

class HealthCheckControllerTest extends TestCase
{
    public function testIndexReturnsOk()
    {
        $controller = new HealthCheckController();
        $response = $controller->index();

        $this->assertEquals('ok', $response->getContent());
    }
}
