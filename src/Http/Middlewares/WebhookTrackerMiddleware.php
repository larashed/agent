<?php

namespace Larashed\Agent\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Larashed\Agent\Events\WebhookExecuted;

class WebhookTrackerMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param null    $source
     * @param null    $name
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $source = null, $name = null)
    {
        event(new WebhookExecuted($request, $source, $name));

        return $next($request);
    }
}
