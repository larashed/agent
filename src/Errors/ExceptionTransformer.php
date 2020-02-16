<?php

namespace Larashed\Agent\Errors;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class ExceptionTransformer
 *
 * @package Larashed\Agent\Helpers
 */
class ExceptionTransformer
{
    /**
     * @var \Exception
     */
    protected $exception;

    /**
     * ExceptionTransformer constructor.
     *
     * @param \Exception $exception
     */
    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $exceptions = [];

        $exceptions[] = [
            'class'   => get_class($this->exception),
            'message' => $this->exception->getMessage(),
            'code'    => $this->exception->getCode(),
            'file'    => $this->exception->getFile(),
            'line'    => $this->exception->getLine(),
            'trace'   => $this->getTraceLines()
        ];

        $previous = $this->exception->getPrevious();

        if (!is_null($previous)) {
            $exceptions = array_merge(
                $exceptions,
                (new static($previous))->toArray()
            );
        }

        return $exceptions;
    }

    protected function getTraceLines()
    {
        $lines = [];

        collect($this->exception->getTrace())->each(function ($trace) use (&$lines) {
            $line = Arr::only($trace, ['file', 'line', 'function', 'class']);

            $codeSnippet = [];

            if (isset($trace['file']) && isset($trace['line'])) {
                $codeSnippet = (new CodeSnippet($trace['file'], $trace['line']))->get();
            }

            $line['snippet'] = $codeSnippet;

            $lines[] = $line;
        });
        return $lines;
    }
}
