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
            'method' => $this->request->getMethod()
        ];

        $data = array_merge($data, $this->getRouteData());
        $data = array_merge($data, $this->getUserData());

        return $data;
    }

    /**
     * @return array
     */
    protected function getRouteData()
    {
        $data = [];
        $data['name'] = '';
        $data['action'] = '';

        $route = $this->request->route();

        if (!is_null($route)) {
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
        $data = ['user_id' => 0];
        $user = $this->request->user(config('larashed.agent.auth.guard'));

        if (!is_null($user)) {
            $data['user_id'] = $user->getAuthIdentifier();
        }

        return $data;
    }

}
