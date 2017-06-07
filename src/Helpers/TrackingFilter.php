<?php

namespace Larashed\Agent\Helpers;

use Illuminate\Routing\Route;

class TrackingFilter
{
    public function shouldSkipRoutes(Route $route = null)
    {
        if (is_null($route)) {
            return false;
        }

        if (str_is('debugbar.*', $route->getName())) {
            return false;
        }

        return true;
    }
}
