<?php

namespace Larashed\Agent\Events;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

    /**
     * @var Response
     */
    public $response;

    /**
     * RequestExecuted constructor.
     *
     * @param Request  $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
