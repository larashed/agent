<?php

namespace Larashed\Agent\Trackers\Server;

use Illuminate\Support\Str;
use Larashed\Agent\System\System;

/**
 * Class ServiceCollector
 *
 * @package Larashed\Agent\Trackers\Server
 */
class ServiceCollector
{
    /**
     * @var System
     */
    protected $system;

    /**
     * ServiceCollector constructor.
     *
     * @param System $system
     */
    public function __construct(System $system)
    {
        $this->system = $system;
    }

    /**
     * @return array
     */
    public function services()
    {
        $output = $this->system->exec('service --status-all 2>/dev/null | grep \'[ + ]\'');

        $lines = explode("\n", $output);

        $services = collect($lines)->filter(function ($line) {
            return Str::contains($line, '[ + ]');
        })->map(function ($line) {
            $line = trim(str_replace('[ + ] ', '', $line));

            return $line;
        });

        return $services->values()->toArray();
    }
}
