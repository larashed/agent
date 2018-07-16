<?php

namespace Larashed\Agent\Trackers\Traits;

/**
 * Trait MemoryCalculationTrait
 *
 * @package Larashed\Agent\Trackers\Traits
 */
trait MemoryCalculationTrait
{
    /**
     * @var float
     */
    protected $startMemory;

    /**
     * @var float
     */
    protected $memory;

    /**
     * @param $startMemory
     *
     * @return $this
     */
    public function setStartMemoryUsage($startMemory)
    {
        $this->startMemory = (int) $startMemory;

        return $this;
    }

    /**
     * @param $endMemory
     *
     * @return $this
     */
    public function setMemoryUsage($endMemory)
    {
        $this->memory = round((int) $endMemory - $this->startMemory);

        return $this;
    }
}
