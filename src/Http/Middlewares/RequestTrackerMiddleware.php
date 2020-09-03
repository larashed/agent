<?php

namespace Larashed\Agent\Http\Middlewares;

use Illuminate\Support\Str;
use Larashed\Agent\Agent;
use Larashed\Agent\AgentConfig;
use Larashed\Agent\Events\RequestExecuted;
use Larashed\Agent\System\Measurements;

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
     * @var Measurements
     */
    protected $measurements;

    /**
     * Terminating constructor.
     *
     * @param Agent       $agent
     * @param AgentConfig $config
     */
    public function __construct(Agent $agent, AgentConfig $config, Measurements $measurements)
    {
        $this->agent = $agent;
        $this->config = $config;
        $this->measurements = $measurements;
    }

    /**
     * @param          $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $requestStartTime = $this->measurements->microtime();

        if (defined('LARAVEL_START')) {
            $requestStartTime = LARAVEL_START;
        }

        $response = $next($request);

        $requestIgnored = Str::contains($request->getUri(), $this->config->getIgnoredEndpoints());
        if (!$requestIgnored) {
            event(new RequestExecuted($request, $response, $requestStartTime));
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
