<?php

namespace Larashed\Agent\Events;

use Illuminate\Http\Request;

/**
 * Class WebhookExecuted
 *
 * @package Larashed\Agent\Events
 */
class WebhookExecuted
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var string
     */
    public $source;

    /**
     * @var string
     */
    public $name;

    /**
     * WebhookExecuted constructor.
     *
     * @param Request $request
     * @param string  $source
     * @param string  $name
     */
    public function __construct(Request $request, $source, $name)
    {
        $this->request = $request;
        $this->source = $source;
        $this->name = $name;
    }
}
