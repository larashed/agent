<?php

namespace Larashed\Agent\Trackers\Artisan;

use Exception;
use Larashed\Agent\Errors\ExceptionTransformer;
use Larashed\Agent\Trackers\Traits\TimeCalculationTrait;
use Symfony\Component\Console\Input\InputInterface;

class Command
{
    use TimeCalculationTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var Exception
     */
    protected $exception;

    /**
     * @var int
     */
    protected $exitCode;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return Command
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return Command
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     *
     * @return Command
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @return int
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }

    /**
     * @param int $exitCode
     *
     * @return Command
     */
    public function setExitCode($exitCode)
    {
        $this->exitCode = $exitCode;

        return $this;
    }

    /**
     * @return Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param Exception $exception
     *
     * @return Command
     */
    public function setException(Exception $exception)
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param InputInterface $input
     *
     * @return Command
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;

        return $this;
    }

    public function toArray()
    {
        $command = [
            'name'         => $this->name,
            'arguments'    => $this->arguments,
            'options'      => $this->options,
            'created_at'   => $this->createdAt,
            'processed_in' => $this->processedIn,
            'exit_code'    => $this->exitCode,
            'exception'    => null
        ];

        if (!is_null($this->exception)) {
            $transformer = new ExceptionTransformer($this->exception);
            $command['exception'] = $transformer->toArray();
        }

        return $command;
    }
}
