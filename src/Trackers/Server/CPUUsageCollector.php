<?php

namespace Larashed\Agent\Trackers\Server;

use Larashed\Agent\System\System;

/**
 * Class CpuCollector
 *
 * @package Larashed\Agent\Trackers\Server
 */
class CPUUsageCollector
{
    protected $system;

    public function __construct(System $system)
    {
        $this->system = $system;
    }

    /**
     * @return float|null
     */
    public function cpu()
    {
        return $this->calculateCpuAverage();
    }

    /**
     * @return float
     */
    protected function calculateCpuAverage()
    {
        $props = $this->getMetrics($this->getCpuLine());
        sleep(1);
        $props2 = $this->getMetrics($this->getCpuLine());

        $total = $this->getTotalCpuTime($props2) - $this->getTotalCpuTime($props);

        $used = $this->getUsedCpuTime($props2) - $this->getUsedCpuTime($props);

        return round($used / $total * 100, 2);
    }

    /**
     * @param string $cpuLine
     *
     * @return array
     */
    protected function getMetrics($cpuLine)
    {
        $line = explode(" ", preg_replace("!cpu +!", "", $cpuLine));

        return [
            'user'   => $line[0],
            'nice'   => $line[1],
            'system' => $line[2],
            'idle'   => $line[3],
        ];
    }

    protected function getUsedCpuTime(array $properties)
    {
        $total = $properties['user']
            + $properties['nice']
            + $properties['system'];

        return $total;
    }

    /**
     * @param array $properties
     *
     * @return int
     */
    protected function getTotalCpuTime(array $properties)
    {
        $total = $properties['user']
            + $properties['nice']
            + $properties['system']
            + $properties['idle'];

        return $total;
    }

    /**
     * @return mixed
     */
    protected function getCpuLine()
    {
        $stats = $this->system->fileContents('/proc/stat');
        $split = explode("\n", $stats);

        return $split[0];
    }
}
