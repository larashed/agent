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
     * UNIX timestamp of last boot
     *
     * @return false|int
     */
    public function uptime()
    {
        if ($this->system->getOS() === System::OS_OSX) {
            $output = $this->system->exec('sysctl -a | grep kern.boottime');

            if (preg_match('/sec = (\d+)/', $output, $matches)) {
                return (int) $matches[1];
            }
        }

        $output = $this->system->fileContents('/proc/stat');
        if (preg_match('/btime\s(\d+)/', $output, $matches)) {
            return trim($matches[1]);
        }

        return false;
    }

    /**
     * $os = [
     *   'id',
     *   'version',
     *   'pretty_name',
     *   'name'
     * ]
     *
     * @return array
     */
    public function osInformation()
    {
        if ($this->system->getOS() === System::OS_OSX) {
            return $this->osxInformation();
        }

        return $this->linuxInformation();
    }

    /**
     * @return array
     */
    protected function osxInformation()
    {
        $os = $this->parseOsInformation(
            $this->system->exec('sw_vers'),
            '/([a-z_]+):\t(.*)/i',
            [
                'buildversion'   => 'id',
                'productversion' => 'version',
                'productname'    => 'pretty_name',
            ]
        );

        $os['name'] = 'OSX';

        return $os;
    }

    /**
     * @return array
     */
    protected function linuxInformation()
    {
        return $this->parseOsInformation(
            $this->system->fileContents('/etc/os-release'),
            '/([a-z_]+)=(.*)/i',
            [
                'id'          => 'id',
                'name'        => 'name',
                'version_id'  => 'version',
                'pretty_name' => 'pretty_name',
            ]
        );
    }

    /**
     * @param $input
     * @param $regex
     * @param $keys
     *
     * @return array
     */
    protected function parseOsInformation($input, $regex, $keys)
    {
        $lines = collect(explode("\n", $input));
        $values = collect([]);

        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match($regex, $line, $matches)) {
                $key = strtolower($matches[1]);
                $value = str_replace('"', '', $matches[2]);

                $values[$key] = $value;
            }
        }

        $os = [];

        foreach ($keys as $fromKey => $toKey) {
            $os[$toKey] = $values->get($fromKey);
        }

        return $os;
    }
}
