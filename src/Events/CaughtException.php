<?php

namespace Larashed\Agent\Events;

use Throwable;

class CaughtException
{
    /**
     * @var Throwable
     */
    public $exception;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
    }
}
