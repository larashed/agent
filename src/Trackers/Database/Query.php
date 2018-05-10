<?php

namespace Larashed\Agent\Trackers\Database;

use Illuminate\Database\Events\QueryExecuted;
use Larashed\Agent\System\Measurements;
use Larashed\Agent\Trackers\Traits\TimeCalculationTrait;

/**
 * Class Job
 *
 * @package Larashed\Agent\Trackers\Queue
 */
class Query
{
    use TimeCalculationTrait;

    /**
     * @var QueryExecuted
     */
    protected $query;

    /**
     * @var Measurements
     */
    protected $metrics;

    /**
     * @var array
     */
    protected $statement;

    /**
     * @var string
     */
    protected $connection;

    /**
     * Query constructor.
     *
     * @param Measurements  $metrics
     * @param QueryExecuted $query
     */
    public function __construct(Measurements $metrics, QueryExecuted $query)
    {
        $this->metrics = $metrics;
        $this->setAttributes($query);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'created_at'   => $this->createdAt,
            'query'        => $this->statement,
            'connection'   => $this->connection,
            'processed_in' => $this->processedIn
        ];
    }

    /**
     * @param QueryExecuted $query
     *
     * @return mixed
     */
    protected function setAttributes(QueryExecuted $query)
    {
        $this->setCreatedAt($this->metrics->time());
        $this->statement = $query->sql;
        $this->connection = $query->connectionName;
        $this->processedIn = $query->time;

        return $this;
    }
}
