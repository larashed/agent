<?php

namespace Larashed\Agent\Trackers\Http;

use Illuminate\Http\Request;
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
     * @var Request
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
     * @param Request      $request
     * @param null         $source
     * @param null         $name
     */
    public function __construct(Measurements $measurements, Request $request, $source = null, $name = null)
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
            'name'       => $this->name,
            'source'     => $this->source,
            'payload'    => $this->getPayload($this->request)
        ];
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getPayload(Request $request)
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
     * @param Request $request
     *
     * @return array
     */
    protected function getHeaders(Request $request)
    {
        $headers = collect($request->header())->map(function ($header) {
            return $header[0];
        });

        unset($headers['cookie']);

        return $headers->toArray();
    }
}
