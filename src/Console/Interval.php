<?php

namespace Larashed\Agent\Console;

/**
 * Class Interval
 * @package Larashed\Agent\Console
 */
class Interval
{
    /**
     * @var int
     */
    protected $seconds;

    /**
     * @var
     */
    protected $startTime;
    /**
     * @var
     */
    protected $endTime;

    /**
     * Interval constructor.
     *
     * @param int $seconds
     */
    public function __construct($seconds = 60)
    {
        $this->seconds = $seconds;
    }

    /**
     * @return $this
     */
    public function start()
    {
        $this->startTime = microtime(true);

        return $this;
    }

    /**
     * @return bool
     */
    public function passed()
    {
        return microtime(true) - $this->startTime > $this->seconds;
    }

    /**
     * @return Interval
     */
    public function restart()
    {
        return $this->start();
    }
}
