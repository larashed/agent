<?php

namespace Larashed\Agent\Console;

use Illuminate\Contracts\Filesystem\Filesystem;

class DaemonRestartHandler
{
    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var string
     */
    protected $file;

    public function __construct(Filesystem $fs, $file)
    {
        $this->fs = $fs;
        $this->file = $file;
    }

    public function check()
    {
        return $this->fs->exists($this->file);
    }

    public function markNeeded()
    {
        return $this->fs->put($this->file, time());
    }

    public function markComplete()
    {
        return $this->fs->delete($this->file);
    }
}
