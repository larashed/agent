<?php

namespace Larashed\Agent\System;

use Carbon\Carbon;

/**
 * Class Measurements
 *
 * @codeCoverageIgnore
 *
 * @package Larashed\Agent\Trackers\Environment
 */
class Measurements
{
    /**
     * @return float
     */
    public function microtime()
    {
        return microtime(true);
    }

    /**
     * @param null $timestamp
     *
     * @return string
     */
    public function militime($timestamp = null)
    {
        if (!is_null($timestamp)) {
            return Carbon::createFromTimestampUTC(round($timestamp, 0))->getPreciseTimestamp(3);
        }

        return Carbon::now()->getPreciseTimestamp(3);
    }

    /**
     * @param null $timestamp
     *
     * @return string
     */
    public function time($timestamp = null)
    {
        if (!is_null($timestamp)) {
            return Carbon::createFromTimestampUTC(round($timestamp, 0))->format('c');
        }

        return Carbon::now()->format('c');
    }

    /**
     * @return int
     */
    public function memory()
    {
        return memory_get_usage(false);
    }

    /**
     * @param $start
     * @param $end
     *
     * @return float
     */
    public function microtimeDiff($start, $end)
    {
        return round(($end - $start) * 1000);
    }
}
