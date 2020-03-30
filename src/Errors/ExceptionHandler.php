<?php

namespace Larashed\Agent\Errors;

use App\Exceptions\Handler;
use Exception;
use Larashed\Agent\Events\CaughtException;

class ExceptionHandler extends Handler
{
    public function report(Exception $exception)
    {
        app('events')->dispatch(new CaughtException($exception));

        parent::report($exception);
    }
}
