<?php

namespace Larashed\Agent\Errors;

use App\Exceptions\Handler;
use Larashed\Agent\Events\CaughtException;
use Throwable;

class ExceptionHandler extends Handler
{
    public function report(Throwable $exception)
    {
        app('events')->dispatch(new CaughtException($exception));

        parent::report($exception);
    }
}
