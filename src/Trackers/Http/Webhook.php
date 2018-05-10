<?php

namespace Larashed\Agent\Trackers\Http;

use Illuminate\Http\Request as BaseRequest;
use Larashed\Agent\System\Measurements;
use Larashed\Agent\Trackers\Traits\TimeCalculationTrait;

/**
 * Class Webhook
 *
 * @package Larashed\Agent\Trackers\Http
 */
class Webhook
{
    use TimeCalculationTrait;

    /**
     * @var Measurements
     */
    protected $measurements;

    /**
     * @var BaseRequest
     */
    protected $request;

    /**
     * @var null
     */
    protected $name;

    /**
     * @var null
     */
    protected $source;

    /**
     * @var array
     */
    protected $payload;

    /**
     * Webhook constructor.
     *
     * @param Measurements $measurements
     * @param BaseRequest  $request
     * @param null         $source
     * @param null         $name
     */
    public function __construct(Measurements $measurements, BaseRequest $request, $source = null, $name = null)
    {
        $this->measurements = $measurements;
        $this->request = $request;
        $this->name = $name;
        $this->source = $source;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name'    => $this->name,
            'source'  => $this->source,
            'payload' => $this->getPayload($this->request)
        ];
    }

    /**
     * @param BaseRequest $request
     *
     * @return array
     */
    protected function getPayload(BaseRequest $request)
    {
        $payload = [
            'url'     => $request->fullUrl(),
            'method'  => $request->getMethod(),
            'body'    => base64_encode($request->getContent()),
            'headers' => $this->getHeaders($request)
        ];

        return $payload;
    }

    /**
     * @param BaseRequest $request
     *
     * @return array
     */
    protected function getHeaders(BaseRequest $request)
    {
        $headers = collect($request->header())->map(function ($header) {
            return $header[0];
        });

        unset($headers['cookie']);

        return $headers->toArray();
    }
}
