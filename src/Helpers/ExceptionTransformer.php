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
     * @return \Exception
     */
    public function toArray()
    {
        $result = [
            'message' => $this->exception->getMessage(),
            'code'    => $this->exception->getCode(),
            'file'    => $this->exception->getFile(),
            'line'    => $this->exception->getLine(),
            'trace'   => $this->exception->getTrace()
        ];

        $previous = $this->exception->getPrevious();

        if (!is_null($previous)) {
            $result['previous'] = new static($previous);
        }

        return $previous;
    }
}
