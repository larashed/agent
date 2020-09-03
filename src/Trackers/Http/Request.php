<?php

namespace Larashed\Agent\Trackers\Http;

use Illuminate\Http\Request as BaseRequest;
use Larashed\Agent\System\Measurements;

/**
 * Class Request
 *
 * @package Larashed\Agent\Trackers\Http
 */
class Request
{
    /**
     * @var BaseRequest
     */
    protected $request;

    /**
     * @var Measurements
     */
    protected $measurements;

    /**
     * @var float
     */
    protected $requestStartTime;

    /**
     * Request constructor.
     *
     * @param Measurements $measurements
     * @param BaseRequest  $request
     * @param float        $requestStartTime
     */
    public function __construct(Measurements $measurements, BaseRequest $request, $requestStartTime)
    {
        $this->measurements = $measurements;
        $this->request = $request;
        $this->requestStartTime = $requestStartTime;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = [
            'created_at'   => $this->measurements->time($this->requestStartTime),
            'processed_in' => $this->measurements->microtimeDiff($this->requestStartTime, $this->measurements->microtime()),
            'url'          => $this->request->getUri(),
            'method'       => $this->request->getMethod(),
            'route'        => $this->getRouteData(),
            'user'         => $this->getUserData(),
            'meta'         => $this->getMetaData(),
        ];

        return $data;
    }

    /**
     * @return array
     */
    protected function getRouteData()
    {
        $data = [];
        $data['uri'] = null;
        $data['name'] = null;
        $data['action'] = null;

        $route = $this->request->route();

        if (!is_null($route)) {
            $data['uri'] = $route->uri();
            $data['name'] = $route->getName();
            $data['action'] = $route->getActionName();
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getUserData()
    {
        $data = ['id' => 0, 'name' => null];

        if (!config('larashed.user.enabled')) {
            return $data;
        }

        $guard = config('larashed.user.guard', config('auth.defaults.guard'));

        $user = $this->request->user($guard);

        if (!is_null($user)) {
            $data['id'] = $user->getAuthIdentifier();
            $data['name'] = data_get($user, 'name');
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getMetaData()
    {
        $headers['referrer'] = $this->request->header('referer');
        $headers['user-agent'] = $this->request->header('user-agent');
        $headers['ip'] = $this->request->getClientIp();

        return $headers;
    }
}
