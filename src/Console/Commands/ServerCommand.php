<?php

namespace Larashed\Agent\Console\Commands;

use Illuminate\Console\Command;
use Larashed\Agent\Api\LarashedApi;
use Larashed\Agent\Trackers\ServerEnvironmentTracker;

/**
 * Class ServerCommand
 *
 * @package Larashed\Agent\Console\Commands
 */
class ServerCommand extends Command
{
    /**
     * @var ServerEnvironmentTracker
     */
    protected $tracker;

    /**
     * @var LarashedApi
     */
    protected $api;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larashed:server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collects and sends server resource and service data';

    /**
     * ServerCommand constructor.
     *
     * @param ServerEnvironmentTracker $tracker
     * @param LarashedApi              $api
     */
    public function __construct(ServerEnvironmentTracker $tracker, LarashedApi $api)
    {
        $this->tracker = $tracker;
        $this->api = $api;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $data = $this->tracker->gather();

        try {
            $this->api->sendServerData($data);
        } catch (\Exception $exception) {
            $this->error('Failed to send collected server data.');
        }

        $this->info('Successfully sent collected server data.');
    }
}
