<?php

namespace Larashed\Agent\Http;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Illuminate\Http\Response as LaravelResponse;
use Illuminate\Http\RedirectResponse as LaravelRedirectResponse;

/**
 * Class Response
 *
 * @package Larashed\Admin\App\Libraries
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
     * @var LaravelResponse|LaravelRedirectResponse
     */
    protected $content;

    /**
     * @var string|null
     */
    protected $type;

    /**
     * @var integer|null
     */
    protected $code;

    /**
     * Response constructor.
     *
     * @param $response
     */
    public function __construct($response)
    {
        $this->setResponseAttributes($response);
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->code;
    }

    /**
     * @return LaravelRedirectResponse|LaravelResponse
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return null|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'type'         => $this->getType(),
            'content'      => $this->getContent(),
            'code'         => $this->getStatusCode(),
            'processed_in' => microtime(true) - LARAVEL_START,
            'received_at'  => Carbon::now()->setTimezone('UTC')->toDateTimeString()
        ];
    }

    /**
     * @param $response
     */
    protected function setResponseAttributes($response)
    {
        if ($response instanceof LaravelResponse) {
            $this->type = $this->getContentType($response);
            $this->content = $this->getResponseContent($response, $this->type);
            $this->code = $response->getStatusCode();
        } elseif ($response instanceof LaravelRedirectResponse) {
            $this->type = self::TYPE_REDIRECT;
            $this->code = $response->getStatusCode();
        } else {
            throw new InvalidArgumentException('Invalid response object');
        }
    }

    /**
     * @param LaravelResponse $response
     *
     * @return null|string
     */
    protected function getContentType(LaravelResponse $response)
    {
        if (!is_null($response->exception)) {
            return self::TYPE_EXCEPTION;
        }

        if ($response->getOriginalContent() instanceof View) {
            return self::TYPE_VIEW;
        }

        if ($response->getOriginalContent() instanceof Collection) {
            return self::TYPE_COLLECTION;
        }

        if (is_array($response->getOriginalContent())) {
            return self::TYPE_ARRAY;
        }

        if (is_string($response->getOriginalContent())) {
            return self::TYPE_MIXED;
        }

        return null;
    }

    /**
     * @param LaravelResponse $response
     * @param                 $type
     *
     * @return mixed|null|string
     */
    protected function getResponseContent(LaravelResponse $response, $type)
    {
        switch ($type) {
            case self::TYPE_EXCEPTION:
                return (string) $response->exception;
            case self::TYPE_VIEW:
                return $response->getOriginalContent()->getName();
            case self::TYPE_COLLECTION:
                // return $response->getOriginalContent()->toArray();
                return null;
            case self::TYPE_ARRAY:
                // return $response->getOriginalContent();
                return null;
            case self::TYPE_MIXED:
                return null;
        }

        return null;
    }
}
