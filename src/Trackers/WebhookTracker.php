<?php

namespace Larashed\Agent\Trackers;

use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class WebhookTracker
 *
 * @package Larashed\Agent\Trackers
 */
class WebhookTracker extends BaseTracker
{
    /**
     * @param Request $request
     * @param null    $source
     * @param null    $name
     */
    public function capture(Request $request, $source = null, $name = null)
    {
        $webhook = [
            'name'       => $name,
            'source'     => $source,
            'payload'    => $this->getPayload($request),
            'created_at' => Carbon::now('UTC')->format('c')
        ];

        $this->agent->getCollector()->addWebhook($webhook);
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
