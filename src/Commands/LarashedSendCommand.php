<?php

namespace Larashed\Agent\Commands;

use Illuminate\Console\Command;
use Larashed\Agent\Storage\StorageFactory;
use Larashed\Api\LarashedApi;

class LarashedSendCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larashed:send {--daemon} {--sleep=3}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var \Larashed\Agent\Storage\AgentStorageInterface
     */
    protected $storage;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->storage = StorageFactory::buildFromConfig();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (is_null($this->storage)) {
            return;
        }

        if ($this->option('daemon')) {
            while ( true ) {
                $this->send();
                sleep($this->option('sleep'));
            }

            return;
        }

        $this->send();
    }

    protected function send()
    {
        /** @var LarashedApi $api */
        $api = app(LarashedApi::class);

        $records = $this->storage->getRecords();

        if ($records->count() > 0) {
            $this->info('Got ' . $records->count() . ' records.');
        }

        $records->each(function ($data, $identifier) use ($api) {
            $response = $api->agent()->send(json_decode($data, true));

            if ($response['success']) {
                $this->storage->remove($identifier);
            } else {
                print_r($response);
            }
        });
    }
}
