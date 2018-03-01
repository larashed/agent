<?php

namespace Larashed\Agent\Commands;

use Illuminate\Console\Command;
use Larashed\Agent\Storage\StorageFactory;
use Larashed\Api\LarashedApi;
use Exception;

class DaemonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larashed:daemon {--single-run : Run the agent only once} {--sleep=10} {--limit=200}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends collected application data to Larashed service';

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

        if (!$this->option('single-run')) {
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

        $limit = $this->option('limit');
        $records = $this->storage->getRecords((int) $limit);

        if ($records->count() === 0) {
            return;
        }

        $data = join("\n", $records->toArray());

        try {
            $response = $api->agent()->send($data);
        } catch (Exception $exception) {
            $this->error('Failed to send due to API error. Will try later.');
            $this->error('Error: ' . $exception->getMessage());

            return;
        }

        if ($response['success'] == true) {
            $this->storage->remove($records->keys()->toArray());

            $this->info('Successfully sent ('.$records->count().' records) ' . strlen($data) . ' bytes of data.');

            return;
        }

        $this->error('Failed to send collected data. Will try again.');
    }
}
