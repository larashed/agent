<?php

namespace Larashed\Agent\Trackers\Server;

use Larashed\Agent\System\System;

/**
 * Class MemoryCollector
 *
 * @package Larashed\Agent\Trackers\Server
 */
class MemoryCollector
{
    /**
     * @var System
     */
    protected $system;

    /**
     * @var string
     */
    protected $memInfo;

    /**
     * MemoryCollector constructor.
     *
     * @param System $system
     */
    public function __construct(System $system)
    {
        $this->system = $system;
    }

    /**
     * Total memory in bytes
     *
     * @return int
     */
    public function total()
    {
        return $this->extract('MemTotal');
    }

    /**
     * Free memory in bytes
     *
     * @return int
     */
    public function free()
    {
        $available = $this->extract('MemAvailable');
        if (!is_null($available)) {
            return $available;
        }

        // fallback for kernels older than 3.14
        $available = (int) $this->extract('MemFree')
            + (int) $this->extract('Buffers')
            + (int) $this->extract('Cached');

        return $available;
    }

    /**
     * @param $prefix
     *
     * @return int
     */
    protected function extract($prefix)
    {
        $pattern = '/' . $prefix . ':\s+(\d+)/';

        $contents = $this->getMemInfo();

        if (preg_match($pattern, $contents, $match)) {
            return (int) $match[1];
        }

        return null;
    }

    /**
     * @return bool|string|null
     */
    protected function getMemInfo()
    {
        if (is_null($this->memInfo)) {
            $this->memInfo = $this->system->fileContents('/proc/meminfo');
        }

        return $this->memInfo;
    }
}
