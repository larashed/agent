<?php

namespace Larashed\Agent\Transport;

use Illuminate\Support\Collection;

/**
 * Interface TransportInterface
 *
 * @package Larashed\Agent\Transport
 */
interface TransportInterface
{
    /**
     * @param array $record
     *
     * @return mixed
     */
    public function push(array $record);

    /**
     * @param $limit
     *
     * @return Collection
     */
    public function records($limit);

    /**
     * @param array $identifiers
     *
     * @return mixed
     */
    public function remove(array $identifiers);
}
