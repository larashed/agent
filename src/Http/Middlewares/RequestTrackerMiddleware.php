<?php

namespace Larashed\Agent\Http\Middlewares;

use Larashed\Agent\Agent;
use Larashed\Agent\Events\RequestExecuted;

/**
 * Class RequestTrackerMiddleware
 *
 * @package Larashed\Agent\Http\Middlewares
 */
class RequestTrackerMiddleware
{
    /**
     * @var Agent
     */
    protected $agent;

    /**
     * Terminating constructor.
     *
     * @param Agent $agent
     */
    public function __construct(Agent $agent)
    {
        $this->agent = $agent;
    }

    /**
     * @param          $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $response = $next($request);

        event(new RequestExecuted($request, $response));

        return $response;
    }

    /**
     * Tell agent to store collected data
     */
    public function terminate($request, $response)
    {
        $this->agent->stop();
    }
}
