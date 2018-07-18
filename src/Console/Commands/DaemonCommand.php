<?php

namespace Larashed\Agent\Console\Commands;

use Exception;
use Larashed\Agent\Console\Interval;
use Larashed\Agent\Console\Sender;
use Larashed\Agent\Api\LarashedApi;
use Larashed\Agent\Storage\StorageInterface;
use Illuminate\Console\Command;

/**
 * Class DaemonCommand
 *
 * @package Larashed\Agent\Console\Commands
 */
class DaemonCommand extends Command
{
    /**
     * @var Sender
     */
    protected $sender;

    /**
     * @var Interval
     */
    protected $interval;

    /**
     * @var boolean
     */
    protected $running = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larashed:daemon 
            {--single-run : run the daemon once}
            {--sleep=5 : sleep interval in seconds}
            {--limit=200 : number of records to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends collected application data to Larashed service';

    /**
     * DaemonCommand constructor.
     *
     * @param Sender    $sender
     * @param  Interval $interval
     */
    public function __construct(Sender $sender, Interval $interval)
    {
        $this->sender = $sender;
        $this->interval = $interval;

        parent::__construct();
    }

    /**
     * Starts the daemon and sends collected data
     */
    public function handle()
    {
        $recordLimit = $this->getRecordLimit();

        if ($this->isSingleRun()) {
            $this->outputReport($this->sender->send($recordLimit));

            return;
        }

        $this->interval->start();

        while ( $this->shouldRun() ) {
            $this->outputReport($this->sender->send($recordLimit));

            sleep($this->getSleepSeconds());

            if ($this->interval->passed()) {
                $this->call('larashed:server');

                $this->interval->restart();
            }
        }
    }

    /**
     * @param $shouldRun
     *
     * @return $this
     */
    public function setRunningMode($shouldRun)
    {
        $this->running = (bool) $shouldRun;

        return $this;
    }

    /**
     * @return bool
     */
    protected function shouldRun()
    {
        return $this->running;
    }

    /**
     * @return int
     */
    protected function getRecordLimit()
    {
        return (int) $this->option('limit');
    }

    /**
     * @return bool
     */
    protected function isSingleRun()
    {
        return (bool) $this->option('single-run');
    }

    /**
     * @return int
     */
    protected function getSleepSeconds()
    {
        return (int) $this->option('sleep');
    }

    /**
     * @param bool $succeeded
     */
    protected function outputReport($succeeded)
    {
        if ($succeeded) {
            $this->info('Successfully sent collected data.');

            return;
        }

        $this->error('Failed to send collected data.');
    }
}
