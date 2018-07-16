<?php

namespace Larashed\Agent\Trackers\Http;

use Illuminate\Http\Response as LaravelResponse;
use Illuminate\Http\RedirectResponse as LaravelRedirectResponse;
use Larashed\Agent\Errors\ExceptionTransformer;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class Response
 *
 * @package Larashed\Agent\Trackers\Http
 */
class Response
{
    const TYPE_EXCEPTION  = 'exception';
    const TYPE_VIEW       = 'view';
    const TYPE_COLLECTION = 'collection';
    const TYPE_ARRAY      = 'array';
    const TYPE_REDIRECT   = 'redirect';
    const TYPE_MIXED      = 'mixed';

    /**
     * @var
     */
    protected $response;

    /**
     * @var integer|null
     */
    protected $code;

    /**
     * @var array
     */
    protected $exception;

    /**
     * Response constructor.
     *
     * @param $response
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $attributes = $this->getResponseAttributes($this->response);

        return $attributes;
    }

    /**
     * @param $response
     *
     * @return array
     */
    protected function getResponseAttributes($response)
    {
        $attributes = [
            'code'      => null,
            'exception' => null,
        ];

        if ($response instanceof LaravelResponse) {
            $attributes['code'] = $response->getStatusCode();

            if (!is_null($response->exception)) {
                $transformer = new ExceptionTransformer($response->exception);
                $attributes['exception'] = $transformer->toArray();
            }

            return $attributes;
        }

        if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
            $attributes['code'] = $response->getStatusCode();
        }

        return $attributes;
    }
}
