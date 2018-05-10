<?php

namespace Larashed\Agent\Trackers\Server;

use Larashed\Agent\System\System;

/**
 * Class SystemEnvironmentCollector
 *
 * @package Larashed\Agent\Trackers\Server
 */
class SystemEnvironmentCollector
{
    /**
     * @var System
     */
    protected $system;

    /**
     * SystemEnvironmentCollector constructor.
     *
     * @param System $system
     */
    public function __construct(System $system)
    {
        $this->system = $system;
    }

    /**
     * @return string
     */
    public function phpVersion()
    {
        return $this->system->phpVersion();
    }

    /**
     * @return string
     */
    public function hostname()
    {
        return $this->system->hostname();
    }

    /**
     * @return bool
     */
    public function rebootRequired()
    {
        return $this->system->fileExists('/var/run/reboot-required');
    }

    /**
     * @return false|int
     */
    public function uptime()
    {
        $output = $this->system->exec('uptime -s');

        return strtotime($output);
    }

    /**
     * @return array
     */
    public function osInformation()
    {
        $output = $this->system->fileContents('/etc/os-release');

        $lines = collect(explode("\n", $output));
        $values = collect([]);

        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match('/([A-Z_]+)=(.*)/', $line, $matches)) {
                $key = strtolower($matches[1]);
                $value = str_replace('"', '', $matches[2]);

                $values[$key] = $value;
            }
        }

        $os = [];
        $os['id'] = $values->get('id');
        $os['name'] = $values->get('name');
        $os['version'] = $values->get('version_id');
        $os['pretty_name'] = $values->get('pretty_name');

        return $os;
    }
}
