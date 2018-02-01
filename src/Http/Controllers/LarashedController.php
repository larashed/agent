<?php

namespace Larashed\Agent\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class LarashedController extends BaseController
{
    public function healthCheck()
    {
        return response('OK', 200);
    }
}
