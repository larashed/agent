<?php

namespace Larashed\Agent\Console\Commands;

use Exception;
use Larashed\Agent\Console\DaemonRestartHandler;
use Larashed\Agent\Console\Interval;
use Larashed\Agent\Console\Mutex;
use Larashed\Agent\Console\Sender;
use Illuminate\Console\Command;

/**
 * Class CronCommand
 *
 * @package Larashed\Agent\Console\Commands
 */
class CronCommand extends Command
{
    /**
     * @var Sender
     */
    protected $sender;

    /**
     * @var Mutex
     */
    protected $mutex;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larashed:cron 
            {--limit=200 : number of records to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends collected application data to Larashed service';

    /**
     * CronCommand constructor.
     *
     * @param Sender $sender
     * @param Mutex  $mutex
     */
    public function __construct(Sender $sender, Mutex $mutex)
    {
        $this->sender = $sender;
        $this->mutex = $mutex;

        parent::__construct();
    }

    /**
     * Starts the daemon and sends collected data
     */
    public function handle()
    {
        $recordLimit = $this->getRecordLimit();

        if ($this->mutex->locked()) {
            $this->info("larashed:cron is already running.");
            return;
        }

        while ($this->sender->pendingRecords()) {
            $this->mutex->lock();

            $this->outputReport(
                $this->sender->send($recordLimit)
            );

            $this->callServerCommand();

            $this->mutex->unlock();
        }
    }

    /**
     * @return int
     */
    protected function getRecordLimit()
    {
        return (int) $this->option('limit');
    }

    protected function callServerCommand()
    {
        try {
            $this->call('larashed:server');
        } catch (Exception $exception) {
            $this->error('Failed to send server data.');
        }
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
