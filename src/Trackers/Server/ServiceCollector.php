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
    const STATUS_STOPPED      = 0;
    const STATUS_RUNNING      = 1;
    const STATUS_UNDETERMINED = 2;
    const STATUS_UNKNOWN      = 3;
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
        $output = $this->system->exec('service --status-all 2>&1');

        $lines = explode("\n", trim($output));

        $services = collect($lines)->map(function ($line) {
            return $this->parseLine($line);
        })->reject(null);

        return $services->toArray();
    }

    protected function parseLine($line)
    {
        if (preg_match('/\[\s(.)\s\](.*)/', $line, $matches)) {
            return [
                'status' => $this->parseServiceStatus($matches[1]),
                'name'   => $this->parseServiceName($matches[2]),
            ];
        }

        return null;
    }

    protected function parseServiceStatus($status)
    {
        if ($status === '-') {
            return self::STATUS_STOPPED;
        }

        if ($status === '+') {
            return self::STATUS_RUNNING;
        }

        if ($status === '?') {
            return self::STATUS_UNDETERMINED;
        }

        return self::STATUS_UNKNOWN;
    }

    protected function parseServiceName($name)
    {
        return trim(strtolower($name));
    }
}
