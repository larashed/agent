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
    protected $system;

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
        return $this->extract('MemFree');
    }

    /**
     * @param $prefix
     *
     * @return null|int
     */
    protected function extract($prefix)
    {
        $pattern = '/' . $prefix . ':\s+(\d+)/';
        $contents = $this->system->fileContents('/proc/meminfo');

        if (preg_match($pattern, $contents, $match)) {
            return (int) round($match[1] / 1024);
        }

        return null;
    }
}
