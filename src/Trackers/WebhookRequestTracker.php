<?php

namespace Larashed\Agent\Trackers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherInterface;
use Larashed\Agent\Events\WebhookExecuted;
use Larashed\Agent\System\Measurements;
use Larashed\Agent\Trackers\Http\Webhook;

/**
 * Class WebhookRequestTracker
 *
 * @package Larashed\Agent\Trackers
 */
class WebhookRequestTracker implements TrackerInterface
{
    /**
     * @var Webhook
     */
    protected $webhook;

    /**
     * @var Measurements
     */
    protected $measurements;

    /**
     * @var DispatcherInterface
     */
    protected $events;

    /**
     * WebhookRequestTracker constructor.
     *
     * @param DispatcherInterface $events
     * @param Measurements        $measurements
     */
    public function __construct(DispatcherInterface $events, Measurements $measurements)
    {
        $this->events = $events;
        $this->measurements = $measurements;
    }

    /**
     * @return void
     */
    public function bind()
    {
        $this->events->listen(WebhookExecuted::class, $this->onWebhookExecutedCallback());
    }

    /**
     * @return array
     */
    public function gather()
    {
        if (!is_null($this->webhook)) {
            return $this->webhook->toArray();
        }

        return [];
    }

    /**
     * @return void
     */
    public function cleanup()
    {
        $this->webhook = null;
    }

    /**
     * @return \Closure
     */
    protected function onWebhookExecutedCallback()
    {
        return function (WebhookExecuted $event) {
            $this->webhook = new Webhook($this->measurements, $event->request, $event->source, $event->name);
        };
    }
}
