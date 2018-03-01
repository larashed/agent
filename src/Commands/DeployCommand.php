<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeployCommand extends Command
{
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
    public function __construct()
    {
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

    protected function terminateDaemon()
    {
        $pids = collect(array_diff(
            $this->exec('pgrep -f larashed'),
            $this->exec('pgrep -f larashed:deploy')
        ));

        $pids->each(function ($pid) {
            $this->exec('kill ' . $pid);
            $this->info('Killed Larashed process (' . $pid . ')');
        });
    }

    /**
     * Exec external command
     *
     * @param $command
     *
     * @return mixed
     */
    protected function exec($command)
    {
        exec($command, $output);
        return $output;
    }
}
