<?php

namespace Larashed\Agent\Tests\Unit\Trackers\Server;

use Larashed\Agent\System\System;
use Larashed\Agent\Trackers\Server\SystemEnvironmentCollector;
use Orchestra\Testbench\TestCase;

class SystemEnvironmentCollectorTest extends TestCase
{
    public function testPhpVersion()
    {
        $system = $this->getSystemEnvironmentCollector('phpVersion', '7.1.12-3');

        $this->assertEquals('7.1.12-3', $system->phpVersion());
    }

    public function testHostname()
    {
        $system = $this->getSystemEnvironmentCollector('hostname', 'local.host');

        $this->assertEquals('local.host', $system->hostname());
    }

    public function testRebootRequired()
    {
        $system = $this->getSystemEnvironmentCollector('fileExists', true);

        $this->assertEquals(true, $system->rebootRequired());
    }

    public function testUptime()
    {
        $system = $this->getSystemEnvironmentCollector('exec', '2018-01-01 00:00:00');

        $this->assertEquals(1514764800, $system->uptime());
    }

    public function testOsInformation()
    {
        $contents = '
        NAME="Ubuntu"
        VERSION="16.04.2 LTS (Xenial Xerus)"
        ID=ubuntu
        ID_LIKE=debian
        PRETTY_NAME="Ubuntu 16.04.2 LTS"
        VERSION_ID="16.04"
        HOME_URL="http://www.ubuntu.com/"
        SUPPORT_URL="http://help.ubuntu.com/"
        BUG_REPORT_URL="http://bugs.launchpad.net/ubuntu/"
        VERSION_CODENAME=xenial
        UBUNTU_CODENAME=xenial';

        $system = $this->getSystemEnvironmentCollector('fileContents', $contents);

        $os = $system->osInformation();

        $this->assertEquals('ubuntu', $os['id']);
        $this->assertEquals('Ubuntu', $os['name']);
        $this->assertEquals('Ubuntu 16.04.2 LTS', $os['pretty_name']);
        $this->assertEquals('16.04', $os['version']);
    }

    protected function getSystemEnvironmentCollector($method, $return)
    {
        $system = \Mockery::mock(System::class);
        $system->shouldReceive($method)->andReturn($return);
        $system->shouldReceive('getOS')->andReturn(System::OS_LINUX);

        $services = new SystemEnvironmentCollector($system);

        return $services;
    }
}
