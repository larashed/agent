<?php

namespace Larashed\Agent\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class HealthCheckController extends BaseController
{
    public function index()
    {
        return response('ok', 200);
    }
}
