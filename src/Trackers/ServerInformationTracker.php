<?php

namespace Larashed\Agent\Trackers;

use Illuminate\Contracts\Foundation\Application;
use Larashed\Agent\System\Measurements;
use Larashed\Agent\Trackers\Server\ServiceCollector;
use Larashed\Agent\Trackers\Server\SystemEnvironmentCollector;
use RuntimeException;

/**
 * Class ServerInformationTracker
 *
 * @package Larashed\Agent\Trackers
 */
class ServerInformationTracker implements TrackerInterface
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Measurements
     */
    protected $measurements;

    /**
     * @var SystemEnvironmentCollector
     */
    protected $systemCollector;

    /**
     * ServerInformationTracker constructor.
     *
     * @param Application                $app
     * @param Measurements               $measurements
     * @param SystemEnvironmentCollector $systemCollector
     */
    public function __construct(
        Application $app,
        Measurements $measurements,
        SystemEnvironmentCollector $systemCollector
    ) {
        $this->app = $app;
        $this->measurements = $measurements;
        $this->systemCollector = $systemCollector;
    }

    public function bind()
    {
        if (!$this->app->runningInConsole()) {
            throw new RuntimeException('Server monitoring cannot be enabled in a web environment');
        }
    }

    /**
     * Gather server environment data
     *
     * @return array
     */
    public function gather()
    {
        $data = [
            'created_at'      => $this->measurements->time(),
            'reboot_required' => $this->systemCollector->rebootRequired(),
            'php_version'     => $this->systemCollector->phpVersion(),
            'hostname'        => $this->systemCollector->hostname(),
            'uptime'          => $this->systemCollector->uptime(),
            'os'              => $this->systemCollector->osInformation(),
        ];

        return $data;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function cleanup()
    {
    }
}
