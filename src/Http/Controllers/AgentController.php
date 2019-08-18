<?php

namespace Larashed\Agent\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Artisan;

class AgentController extends BaseController
{
    public function index()
    {
        Artisan::call('larashed:cron');

        return response('ok', 200);
    }
}
