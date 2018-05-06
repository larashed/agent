<?php

namespace Larashed\Agent\Trackers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherInterface;
use Larashed\Agent\Events\RequestExecuted;
use Larashed\Agent\System\Measurements;
use Larashed\Agent\Trackers\Http\Request;
use Larashed\Agent\Trackers\Http\Response;

/**
 * Class HttpRequestTracker
 *
 * @package Larashed\Agent\Trackers
 */
class HttpRequestTracker implements TrackerInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var DispatcherInterface
     */
    protected $events;

    /**
     * @var Measurements
     */
    protected $measurements;

    /**
     * HttpRequestTracker constructor.
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
     * @return mixed|void
     */
    public function bind()
    {
        $this->events->listen(RequestExecuted::class, $this->onRequestProcessedCallback());
    }

    /**
     * @return array
     */
    public function gather()
    {
        if (is_null($this->request) || is_null($this->response)) {
            return [];
        }

        $request = $this->request->toArray();
        $request['response'] = $this->response->toArray();

        return $request;
    }

    /**
     * @return mixed|void
     */
    public function cleanup()
    {
        $this->request = null;
        $this->response = null;
    }

    /**
     * @return \Closure
     */
    protected function onRequestProcessedCallback()
    {
        return function (RequestExecuted $event) {
            $this->request = new Request($this->measurements, $event->request);
            $this->response = new Response($event->response);
        };
    }
}
