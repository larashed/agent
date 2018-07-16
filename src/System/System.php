<?php

namespace Larashed\Agent\System;

/**
 * Class System
 *
 * @codeCoverageIgnore
 *
 * @package Larashed\Agent\System
 */
class System
{
    /**
     * @param $command
     *
     * @return string
     */
    public function exec($command)
    {
        return shell_exec($command);
    }

    /**
     * @param $file
     *
     * @return bool
     */
    public function fileExists($file)
    {
        return file_exists($file);
    }

    /**
     * @param $file
     *
     * @return bool|null|string
     */
    public function fileContents($file)
    {
        if (!file_exists($file)) {
            return null;
        }

        return file_get_contents($file);
    }

    /**
     * @return string
     */
    public function hostname()
    {
        return gethostname();
    }

    /**
     * @return string
     */
    public function phpVersion()
    {
        return phpversion();
    }

    /**
     * @param $path
     *
     * @return bool|float
     */
    public function totalDiskSpace($path)
    {
        return disk_total_space($path);
    }

    /**
     * @param $path
     *
     * @return bool|float
     */
    public function freeDiskSpace($path)
    {
        return disk_free_space($path);
    }
}
