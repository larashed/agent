<?php

namespace Larashed\Agent\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Larashed\Agent\Agent;

class TrackRequests
{
    /**
     * TrackRequests constructor.
     *
     * @param Agent $agent
     */
    public function __construct(Agent $agent)
    {
        $this->agent = $agent;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $this->agent->trackHttpData($request, $response);

        return $response;
    }

    /**
     * @param Request  $request
     * @param Response $response
     */
    public function terminate($request, $response)
    {
        $this->agent->terminate();
    }
}
