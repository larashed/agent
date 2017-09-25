<?php

namespace Larashed\Agent\Helpers;

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
    public function __construct(\Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [
            'message' => $this->exception->getMessage(),
            'code'    => $this->exception->getCode(),
            'file'    => $this->exception->getFile(),
            'line'    => $this->exception->getLine(),
            'trace'   => $this->getTraceLines()
        ];

        $previous = $this->exception->getPrevious();

        if (!is_null($previous)) {
            $result['previous'] = (new static($previous))->toArray();
        }

        return $result;
    }

    protected function getTraceLines()
    {
        $lines = [];

        collect($this->exception->getTrace())->each(function ($trace) use (&$lines) {
            $line = array_only($trace, ['file', 'line', 'function', 'class']);
            $lines[] = $line;

            if (str_contains(array_get($line, 'class'), ['App\Http\Controllers', 'App\Jobs'])) {
                return false;
            }
        });

        return $lines;
    }
}
