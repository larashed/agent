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
     * @var array
     */
    protected $ignoredNamespaces = ['App\Http\Controllers', 'App\Jobs'];

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
     * @param $namespaces
     *
     * @return $this
     */
    public function setIgnoredNamespaces($namespaces)
    {
        $this->ignoredNamespaces = (array) $namespaces;

        return $this;
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

            $codeSnippet = new CodeSnippet($trace['file'], $trace['line']);

            $line = Arr::add($line, 'snippet', $codeSnippet->get());

            $lines[] = $line;

            if (Str::contains(Arr::get($line, 'class'), $this->ignoredNamespaces)) {
                return false;
            }
        });

        return $lines;
    }
}
