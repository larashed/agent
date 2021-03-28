<?php

namespace Larashed\Agent\Bus;

use Illuminate\Bus\Dispatcher as BaseDispatcher;
use Larashed\Agent\Events\JobDispatched;

class Dispatcher extends BaseDispatcher
{
    public function dispatch($command)
    {
        $result = parent::dispatch($command);

        $this->container->get('events')->dispatch(new JobDispatched($command, $result, null, []));

        return $result;
    }
}
