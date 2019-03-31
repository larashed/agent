<?php

namespace Larashed\Agent\Trackers\Server;

use Larashed\Agent\System\System;

/**
 * Class DiskCollector
 *
 * @package Larashed\Agent\Trackers\Server
 */
class DiskCollector
{
    /**
     * @var System
     */
    protected $system;

    /**
     * DiskCollector constructor.
     *
     * @param System $system
     */
    public function __construct(System $system)
    {
        $this->system = $system;
    }

    /**
     * Total disk size in bytes
     *
     * @return int
     */
    public function total()
    {
        return (int) round($this->system->totalDiskSpace(base_path()));
    }

    /**
     * Free disk space in bytes
     *
     * @return int
     */
    public function free()
    {
        return (int) round($this->system->freeDiskSpace(base_path()));
    }
}
