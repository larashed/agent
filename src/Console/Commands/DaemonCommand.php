<?php

namespace Larashed\Agent\Console\Commands;

use Exception;
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
            {--sleep=10 : sleep interval in seconds}
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
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->sender = $sender;

        parent::__construct();
    }

    /**
     * Starts the daemon and sends collected data
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');

        if ($this->option('single-run')) {
            $this->outputReport($this->sender->send($limit));

            return;
        }

        while ($this->shouldRun()) {
            $this->outputReport($this->sender->send($limit));

            sleep((int) $this->option('sleep'));
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
