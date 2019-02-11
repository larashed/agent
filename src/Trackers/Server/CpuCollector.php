<?php

namespace Larashed\Agent\Trackers\Server;

use Larashed\Agent\System\System;

/**
 * Class CpuCollector
 *
 * @package Larashed\Agent\Trackers\Server
 */
class CpuCollector
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
        try {
            return $this->calculateCpuAverage();
        } catch (\Exception $exception) {
            return 0;
        }
    }

    /**
     * @return float
     */
    protected function calculateCpuAverage()
    {
        $initialProperties = $this->getCpuProperties($this->getCpuLine());
        sleep(1);
        $finalProperties = $this->getCpuProperties($this->getCpuLine());

        $initialTotalTime = $this->getTotalCpuTime($initialProperties);
        $finalTotalTime = $this->getTotalCpuTime($finalProperties);

        $initialIdleTime = $initialProperties['idle'] + $initialProperties['io_wait'];
        $finalIdleTime = $finalProperties['idle'] + $finalProperties['io_wait'];

        $diffIdle = $finalIdleTime - $initialIdleTime;
        $diffTotal = $finalTotalTime - $initialTotalTime;

        $average = (1000 * ($diffTotal - $diffIdle) / $diffTotal + 5) / 10;

        return round($average, 2);
    }

    /**
     * @param string $cpuLine
     *
     * @return array
     */
    protected function getCpuProperties($cpuLine)
    {
        $line = explode(" ", preg_replace("!cpu +!", "", $cpuLine));
        $values = array_pad($line, 10, 0);

        $keys = ['user', 'nice', 'system', 'idle', 'io_wait', 'irq', 'soft_irq', 'steal', 'guest', 'guest_nice'];
        $statistics = array_combine($keys, $values);

        return $statistics;
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
            + $properties['idle']
            + $properties['io_wait']
            + $properties['irq']
            + $properties['soft_irq']
            + $properties['steal'];

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
