<?php

namespace Larashed\Agent\Tests\Integration;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Route;
use Larashed\Agent\Http\Controllers\HealthCheckController;

class RoutesTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        include __DIR__ . '/../../src/routes.php';
    }

    public function testHealthCheckRouteExists()
    {
        /** @var \Illuminate\Routing\RouteCollection $routes */
        $routes = Route::getRoutes();
        $this->assertNotEmpty($routes->getByAction(HealthCheckController::class . '@index'));
    }

    public function testHealthCheckRouteReturnOk()
    {
        $response = $this->call('get', '/larashed/health-check');
        $response->assertSuccessful();
        $response->assertSee('ok');
    }
}
