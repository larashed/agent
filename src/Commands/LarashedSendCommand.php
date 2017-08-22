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
    protected $signature = 'larashed:send {--daemon} {--sleep=10}';

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

        $records = $this->storage->getRecords(100);

        if ($records->count() === 0) {
            return;
        }

        $this->info('Got ' . $records->count() . ' records.');

        $data = join("\n", $records->toArray());

        $response = $api->agent()->send($data);

        if ($response['success']) {
            $this->storage->remove($records->keys()->toArray());

            $this->info('Successfully sent '.strlen($data) . ' bytes of data.');

            return;
        }

        $this->info('Failed to send collected data. Will try again.');
    }
}
