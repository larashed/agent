<?php

namespace Larashed\Agent\Traits;

use Illuminate\Http\Request;
use Larashed\Agent\Trackers\WebhookTracker;

trait WebhookCaptureTrait
{
    public function capture(Request $request, $source = null, $name = null)
    {
        app(WebhookTracker::class)->capture($request, $source, $name);
    }
}
