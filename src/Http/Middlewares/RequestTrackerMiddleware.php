<?php

namespace Larashed\Agent\Http\Middlewares;

use Illuminate\Support\Str;
use Larashed\Agent\Agent;
use Larashed\Agent\AgentConfig;
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
     * @var AgentConfig
     */
    protected $config;

    /**
     * Terminating constructor.
     *
     * @param Agent       $agent
     * @param AgentConfig $config
     */
    public function __construct(Agent $agent, AgentConfig $config)
    {
        $this->agent = $agent;
        $this->config = $config;
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

        $requestIgnored = Str::contains($request->getUri(), $this->config->getIgnoredEndpoints());
        if (!$requestIgnored) {
            event(new RequestExecuted($request, $response));
        }

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
