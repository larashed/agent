<?php

namespace Larashed\Agent\Trackers;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Events\Dispatcher as DispatcherInterface;
use Illuminate\Support\Carbon;
use Larashed\Agent\Agent;
use Larashed\Agent\Events\CaughtException;
use Larashed\Agent\System\Measurements;
use Larashed\Agent\Trackers\Artisan\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class ArtisanCommandTracker
 *
 * @package Larashed\Agent\Trackers
 */
class ArtisanCommandTracker implements TrackerInterface
{
    /**
     * @var DispatcherInterface
     */
    protected $events;

    /**
     * @var Measurements
     */
    protected $measurements;

    /**
     * @var Agent
     */
    protected $agent;

    /**
     * @var Command
     */
    protected $command;

    /**
     * HttpRequestTracker constructor.
     *
     * @param DispatcherInterface $events
     * @param Measurements        $measurements
     */
    public function __construct(Agent $agent, DispatcherInterface $events, Measurements $measurements)
    {
        $this->agent = $agent;
        $this->events = $events;
        $this->measurements = $measurements;
        $this->command = new Command();
    }

    /**
     * @return mixed|void
     */
    public function bind()
    {
        if (!app()->runningInConsole()) {
            return;
        }

        $this->events->listen(CommandStarting::class, $this->onCommandStartingCallback());
        $this->events->listen(CommandFinished::class, $this->onCommandFinishingCallback());
        $this->events->listen(CaughtException::class, $this->onCommandFailedCallback());
    }

    /**
     * @return array
     */
    public function gather()
    {
        return $this->command->toArray();
    }

    /**
     * @return mixed|void
     */
    public function cleanup()
    {
        $this->command = new Command();
    }

    /**
     * @return \Closure
     */
    protected function onCommandStartingCallback()
    {
        return function (CommandStarting $event) {
            $startedAt = $this->measurements->microtime();

            $this->command->setInput($event->input);
            $this->command->setStartedAt($startedAt);
            $this->command->setCreatedAt(
                Carbon::createFromTimestampUTC(round($startedAt))->format('c')
            );
            $this->command->setName($event->command);
        };
    }

    /**
     * @return \Closure
     */
    protected function onCommandFinishingCallback()
    {
        return function (CommandFinished $event) {
            $this->command->setArguments($this->getArguments($event->input));
            $this->command->setOptions($this->getOptions($event->input));
            $this->command->setProcessedIn($this->measurements->microtime());
            $this->command->setExitCode($event->exitCode);

            $this->agent->stop();
        };
    }

    /**
     * @return \Closure
     */
    protected function onCommandFailedCallback()
    {
        return function (CaughtException $exceptionEvent) {
            $input = $this->command->getInput();

            $this->command->setArguments($this->getArguments($input));
            $this->command->setOptions($this->getOptions($input));

            $this->command->setProcessedIn($this->measurements->microtime());
            $this->command->setException($exceptionEvent->exception);
            $this->command->setExitCode(1);

            $this->agent->stop();
        };
    }

    /**
     * @param InputInterface $input
     *
     * @return array
     */
    protected function getArguments(InputInterface $input)
    {
        $arguments = $input->getArguments();
        unset($arguments['command']);

        return $arguments;
    }

    /**
     * @param InputInterface $input
     *
     * @return array
     */
    protected function getOptions(InputInterface $input)
    {
        $defaultOptions = $this->getDefaultCommandOptions();
        $options = $input->getOptions();

        $difference = array_merge(
            array_diff($options, $defaultOptions),
            array_diff($defaultOptions, $options)
        );

        return $difference;
    }

    /**
     * @return array
     */
    protected function getDefaultCommandOptions()
    {
        return [
            "help"           => false,
            "quiet"          => false,
            "verbose"        => false,
            "version"        => false,
            "ansi"           => false,
            "no-ansi"        => false,
            "no-interaction" => false,
            "env"            => null
        ];
    }
}
