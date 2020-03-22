<?php

namespace Larashed\Agent\Trackers;

use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher as DispatcherInterface;

/**
 * Class LogTracker
 *
 * @package Larashed\Agent\Trackers
 */
class LogTracker implements TrackerInterface
{
    protected $levels = [
        'debug',
        'info',
        'notice',
        'warning',
        'error',
        'critical',
        'alert',
        'emergency',
    ];

    protected $logs = [];

    /**
     * @var DispatcherInterface
     */
    protected $events;

    /**
     * HttpRequestTracker constructor.
     *
     * @param DispatcherInterface $events
     */
    public function __construct(DispatcherInterface $events)
    {
        $this->events = $events;
    }

    /**
     * @return mixed|void
     */
    public function bind()
    {
        $eventName = 'Illuminate\Log\Events\MessageLogged';

        if (class_exists($eventName)) {
            $this->events->listen($eventName, $this->onMessageLoggedCallback());

            return;
        }

        $this->events->listen('illuminate.log', $this->onLogCallback());
    }

    /**
     * @return array
     */
    public function gather()
    {
        return $this->logs;
    }

    /**
     * @return mixed|void
     */
    public function cleanup()
    {
        $this->logs = [];
    }

    /**
     * @return \Closure
     */
    protected function onLogCallback()
    {
        return function ($level, $message, $context) {
            $this->logs[] = [
                'created_at' => Carbon::now()->format('c'),
                'order'      => count($this->logs) + 1,
                'level'      => $this->mapLoggingLevel($level),
                'message'    => $message,
                'context'    => $context
            ];
        };
    }

    /**
     * @return \Closure
     */
    protected function onMessageLoggedCallback()
    {
        return function ($event) {
            $this->logs[] = [
                'created_at' => Carbon::now()->format('c'),
                'order'      => count($this->logs) + 1,
                'level'      => $this->mapLoggingLevel($event->level),
                'message'    => $event->message,
                'context'    => $event->context
            ];
        };
    }

    protected function mapLoggingLevel($level)
    {
        $level = strtolower($level);

        if (!in_array($level, $this->levels)) {
            $level = 'custom';
        }

        return $level;
    }
}
