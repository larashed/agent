<?php

namespace Larashed\Agent\Events;

use Illuminate\Http\Request;

/**
 * Class RequestExecuted
 *
 * @package Larashed\Agent\Events
 */
class RequestExecuted
{
    /**
     * @var Request
     */
    public $request;

    public $response;

    public $requestStartTime;

    /**
     * RequestExecuted constructor.
     *
     * @param Request $request
     * @param         $response
     * @param         $requestStartTime
     */
    public function __construct(Request $request, $response, $requestStartTime)
    {
        $this->request = $request;
        $this->response = $response;
        $this->requestStartTime = $requestStartTime;
    }
}
