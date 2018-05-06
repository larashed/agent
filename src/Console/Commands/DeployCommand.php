<?php

namespace Larashed\Agent\Console\Commands;

use Illuminate\Console\Command;
use Larashed\Agent\System\System;

/**
 * Class DeployCommand
 *
 * @package Larashed\Agent\Console\Commands
 */
class DeployCommand extends Command
{
    /**
     * @var System
     */
    protected $system;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larashed:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handles application deployment for larashed:daemon';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(System $system)
    {
        $this->system = $system;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->terminateDaemon();
    }

    /**
     * Kill the running larashed commands
     */
    protected function terminateDaemon()
    {
        $pids = collect(array_diff(
            $this->exec('pgrep -f larashed'),
            $this->exec('pgrep -f larashed:deploy')
        ));

        $pids->each(function ($pid) {
            $pid = trim($pid);

            $this->exec('kill ' . $pid);
            $this->info('Killed Larashed process (' . $pid . ')');
        });
    }

    /**
     * @param $command
     *
     * @return array|string
     */
    protected function exec($command)
    {
        $output = $this->system->exec($command);

        $output = explode("\n", $output);

        return $output;
    }
}
