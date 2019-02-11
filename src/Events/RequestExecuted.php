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

    /**
     * RequestExecuted constructor.
     *
     * @param Request $request
     * @param         $response
     */
    public function __construct(Request $request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
