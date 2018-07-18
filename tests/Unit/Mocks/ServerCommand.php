<?php

namespace Larashed\Agent\Tests\Unit\Mocks;

use Illuminate\Console\Command;

class ServerCommand extends Command
{
    protected $signature = 'larashed:server';

    public $calledTimes = 0;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->calledTimes++;
    }
}
