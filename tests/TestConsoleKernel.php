<?php

namespace Larashed\Agent\Tests;

class TestConsoleKernel extends \Illuminate\Foundation\Console\Kernel
{
    public function registerCommand($command)
    {
        $this->getArtisan()->add($command);
    }
}
