<?php

namespace Larashed\Agent\Trackers\Traits;

use Exception;
use Larashed\Agent\Errors\ExceptionTransformer;

/**
 * Trait ExceptionTransformerTrait
 *
 * @package Larashed\Agent\Trackers\Traits
 */
trait ExceptionTransformerTrait
{
    /**
     * @var
     */
    protected $exception;

    /**
     * @param Exception $exception
     *
     * @return $this
     */
    public function setExceptionData(Exception $exception)
    {
        $transformer = new ExceptionTransformer($exception);
        $this->exception = $transformer->toArray();

        return $this;
    }
}
