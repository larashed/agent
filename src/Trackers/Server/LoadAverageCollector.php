<?php

namespace Larashed\Agent\Trackers\Server;

use Larashed\Agent\System\System;

/**
 * Class LoadAverageCollector
 *
 * @package Larashed\Agent\Trackers\Server
 */
class LoadAverageCollector
{
    /**
     * @var System
     */
    protected $system;

    /**
     * LoadAverageCollector constructor.
     *
     * @param System $system
     */
    public function __construct(System $system)
    {
        $this->system = $system;
    }

    /**
     * @return array
     */
    public function load()
    {
        return $this->system->loadAverage();
    }
}
