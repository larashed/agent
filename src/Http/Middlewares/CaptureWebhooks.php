<?php

namespace Larashed\Agent\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Larashed\Agent\Trackers\WebhookTracker;

/**
 * Class CaptureWebhooks
 *
 * @package Larashed\Agent\Http\Middlewares
 */
class CaptureWebhooks
{
    /**
     * @var WebhookTracker
     */
    protected $tracker;

    /**
     * CaptureWebhooks constructor.
     *
     * @param WebhookTracker $tracker
     */
    public function __construct(WebhookTracker $tracker)
    {
        $this->tracker = $tracker;
    }

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
        $this->tracker->capture($request, $source, $name);

        return $next($request);
    }
}
