<?php

namespace Larashed\Agent\Console;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Larashed\Agent\Storage\StorageInterface;
use Larashed\Agent\Api\LarashedApi;

/**
 * Class Reporter
 *
 * @package Larashed\Agent\Console
 */
class Sender
{
    /**
     * @var LarashedApi
     */
    protected $api;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * Sender constructor.
     *
     * @param StorageInterface $storage
     * @param LarashedApi      $api
     */
    public function __construct(StorageInterface $storage, LarashedApi $api)
    {
        $this->storage = $storage;
        $this->api = $api;
    }

    /**
     * @param $limit
     *
     * @return bool
     *
     * @throws \Larashed\Agent\Api\LarashedApiException
     */
    public function send($limit)
    {
        $records = $this->storage->records($limit);

        if ($records->count() === 0) {
            return true;
        }

        $data = join("\n", $records->toArray());
        $response = $this->api->sendAppData($data);

        return $this->removeRecordsIfSendingSucceeded($records, $response);
    }

    /**
     * @param Collection $records
     * @param array      $response
     *
     * @return bool
     */
    protected function removeRecordsIfSendingSucceeded(Collection $records, $response)
    {
        if (Arr::get($response, 'success', false) === true) {
            $this->storage->remove($records->keys()->toArray());

            return true;
        }

        return false;
    }
}
