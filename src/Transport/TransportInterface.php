<?php

namespace Larashed\Agent\Transport;

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
}
