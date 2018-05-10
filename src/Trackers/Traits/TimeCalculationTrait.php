<?php

namespace Larashed\Agent\Trackers\Traits;

/**
 * Trait TimeCalculationTrait
 *
 * @package Larashed\Agent\Trackers\Traits
 */
trait TimeCalculationTrait
{
    /**
     * Creation time in 'c' format
     *
     * @var string
     */
    protected $createdAt;

    /**
     * Starting time in milliseconds
     *
     * @var float
     */
    protected $startedAt;

    /**
     * Time it took to process an action in milliseconds
     *
     * @var float
     */
    protected $processedIn;

    /**
     * @param float $startedAt
     *
     * @return $this
     */
    public function setStartedAt($startedAt)
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    /**
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @param float $completedAt
     *
     * @return $this
     */
    public function setProcessedIn($completedAt)
    {
        $this->processedIn = round(($completedAt - $this->startedAt) * 1000, 2);

        return $this;
    }
}
