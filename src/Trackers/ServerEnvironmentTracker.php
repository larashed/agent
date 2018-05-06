<?php

namespace Larashed\Agent\Trackers;

use RuntimeException;
use Larashed\Agent\Trackers\Server\SystemEnvironmentCollector;
use Illuminate\Contracts\Foundation\Application;
use Larashed\Agent\Trackers\Server\CpuCollector;
use Larashed\Agent\Trackers\Server\LaravelEnvironmentCollector;
use Larashed\Agent\Trackers\Server\MemoryCollector;
use Larashed\Agent\Trackers\Server\DiskCollector;
use Larashed\Agent\Trackers\Server\ServiceCollector;
use Larashed\Agent\System\Measurements;

/**
 * Class ServerEnvironmentTracker
 *
 * @package Larashed\Agent\Trackers
 */
class ServerEnvironmentTracker implements TrackerInterface
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
     * @var ServiceCollector
     */
    protected $serviceCollector;

    /**
     * @var MemoryCollector
     */
    protected $memoryCollector;

    /**
     * @var MemoryCollector
     */
    protected $diskCollector;

    /**
     * @var CpuCollector
     */
    protected $cpuCollector;

    /**
     * @var LaravelEnvironmentCollector
     */
    protected $laravelCollector;

    /**
     * @var SystemEnvironmentCollector
     */
    protected $systemCollector;

    /**
     * EnvironmentTracker constructor.
     *
     * @param Application                 $app
     * @param Measurements                $measurements
     * @param ServiceCollector            $serviceCollector
     * @param MemoryCollector             $memoryCollector
     * @param DiskCollector               $diskCollector
     * @param CpuCollector                $cpuCollector
     * @param LaravelEnvironmentCollector $laravelCollector
     * @param SystemEnvironmentCollector  $systemCollector
     */
    public function __construct(
        Application $app,
        Measurements $measurements,
        ServiceCollector $serviceCollector,
        MemoryCollector $memoryCollector,
        DiskCollector $diskCollector,
        CpuCollector $cpuCollector,
        LaravelEnvironmentCollector $laravelCollector,
        SystemEnvironmentCollector $systemCollector
    )
    {
        $this->app = $app;
        $this->measurements = $measurements;
        $this->serviceCollector = $serviceCollector;
        $this->memoryCollector = $memoryCollector;
        $this->diskCollector = $diskCollector;
        $this->cpuCollector = $cpuCollector;
        $this->laravelCollector = $laravelCollector;
        $this->systemCollector = $systemCollector;
    }

    public function bind()
    {
        if (!$this->app->runningInConsole()) {
            throw new RuntimeException('Server monitoring tracker cannot be enabled in a web environment');
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
            'created_at' => $this->measurements->time(),

            'app' => [
                'name'            => $this->laravelCollector->appName(),
                'env'             => $this->laravelCollector->environment(),
                'url'             => $this->laravelCollector->url(),
                'drivers'         => [
                    'queue'    => $this->laravelCollector->queueDriver(),
                    'database' => $this->laravelCollector->databaseDriver(),
                    'cache'    => $this->laravelCollector->cacheDriver(),
                    'mail'     => $this->laravelCollector->mailDriver(),
                ],
                'laravel_version' => $this->laravelCollector->laravelVersion(),
            ],

            'system' => [
                'reboot_required' => $this->systemCollector->rebootRequired(),
                'php_version'     => $this->systemCollector->phpVersion(),
                'hostname'        => $this->systemCollector->hostname(),
                'uptime'          => $this->systemCollector->uptime(),
                'os'              => $this->systemCollector->osInformation(),
                'services'        => $this->serviceCollector->services(),
            ],

            'resources' => [
                'cpu'          => $this->cpuCollector->cpu(),
                'memory_total' => $this->memoryCollector->total(),
                'memory_free'  => $this->memoryCollector->free(),
                'disk_total'   => $this->diskCollector->total(),
                'disk_free'    => $this->diskCollector->free(),
            ]
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
