<?php

namespace Larashed\Agent\Console;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * Class Mutex
 *
 * @package Larashed\Agent\Console
 */
class Mutex
{
    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var string
     */
    protected $file;

    /**
     * Mutex constructor.
     *
     * @param Filesystem $fs
     * @param            $file
     */
    public function __construct(Filesystem $fs, $file)
    {
        $this->fs = $fs;
        $this->file = $file;
    }

    /**
     * @return bool
     */
    public function locked()
    {
        $exists = $this->fs->exists($this->file);
        if ($exists) {
            if ($this->hasExpired()) {
                $this->unlock();

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function lock()
    {
        return $this->fs->put($this->file, time());
    }

    /**
     * @return bool
     */
    public function unlock()
    {
        return $this->fs->delete($this->file);
    }

    /**
     * @return bool
     */
    protected function hasExpired()
    {
        $file = file_get_contents($this->file);
        if ($file !== false) {
            return false;
        }

        return Carbon::createFromTimestampUTC($file)
            ->addMinutes(5)
            ->lt(Carbon::now('UTC'));
    }
}
