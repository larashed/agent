<?php

namespace Larashed\Agent\Trackers;

/**
 * Interface TrackerInterface
 *
 * @package Larashed\Agent\Trackers
 */
interface TrackerInterface
{
    /**
     * Bind tracker events
     *
     * @return mixed
     */
    public function bind();

    /**
     * Return tracker collected data
     *
     * @return mixed
     */
    public function gather();

    /**
     * Cleanup tracker
     *
     * @return mixed
     */
    public function cleanup();
}
