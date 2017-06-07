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
            'name'        => $name,
            'source'      => $source,
            'payload'     => $this->getPayload($request),
            'received_at' => Carbon::now('UTC')->format('Y-m-d H:i:s')
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
            'ssl'          => $request->isSecure(),
            'host'         => $request->getHost(),
            'method'       => $request->getMethod(),
            'path'         => $request->path(),
            'query_string' => $request->getQueryString(),
            'full_url'     => $request->fullUrl(),
            'body'         => base64_encode($request->getContent()),
            'headers'      => $this->getHeaders($request),
            'cookies'      => [],
            // we probably don't need this
            // 'server'       => $request->server(),
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
