<?php

namespace Larashed\Agent\Errors;

use Illuminate\Support\Arr;
use Throwable;

/**
 * Class ExceptionTransformer
 *
 * @package Larashed\Agent\Errors
 */
class ExceptionTransformer
{
    /**
     * @var Throwable
     */
    protected $exception;

    /**
     * ExceptionTransformer constructor.
     *
     * @param Throwable $exception
     */
    public function __construct(Throwable $exception)
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
