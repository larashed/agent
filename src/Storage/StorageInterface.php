<?php

namespace Larashed\Agent\Storage;

use Illuminate\Support\Collection;

/**
 * Interface StorageInterface
 *
 * @package Larashed\Agent\Storage
 */
interface StorageInterface
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
