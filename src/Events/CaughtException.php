<?php

namespace Larashed\Agent\Events;

class CaughtException
{
    public $exception;

    public function __construct($exception)
    {
        $this->exception = $exception;
    }
}
