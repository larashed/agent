<?php

namespace Larashed\Agent\Http;

use Illuminate\Http\Request as BaseRequest;

/**
 * Class Request
 *
 * @package Larashed\Agent\Http
 */
class Request
{
    /**
     * @var BaseRequest
     */
    protected $request;

    /**
     * Request constructor.
     *
     * @param BaseRequest $request
     */
    public function __construct(BaseRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = [
            'url'    => $this->request->getUri(),
            'method' => $this->request->getMethod(),
            'route'  => $this->getRouteData(),
            'user'   => $this->getUserData(),
            'meta'   => $this->getMetaData()
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
        $user = $this->request->user(config('larashed.agent.auth.guard'));

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
