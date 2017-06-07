<?php

namespace Larashed\Agent\Trackers;

use Larashed\Agent\Agent;

abstract class BaseTracker
{
    protected $agent;

    public function __construct(Agent $agent)
    {
        $this->agent = $agent;
    }
}
