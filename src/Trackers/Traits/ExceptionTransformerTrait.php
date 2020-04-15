<?php

namespace Larashed\Agent\Trackers\Traits;

use Larashed\Agent\Errors\ExceptionTransformer;
use Throwable;

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
     * @param Throwable $exception
     *
     * @return $this
     */
    public function setExceptionData(Throwable $exception)
    {
        $transformer = new ExceptionTransformer($exception);
        $this->exception = $transformer->toArray();

        return $this;
    }
}
