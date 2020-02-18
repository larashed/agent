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
    protected $measurements;

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
     * @param Measurements  $measurements
     * @param QueryExecuted $query
     */
    public function __construct(Measurements $measurements, QueryExecuted $query)
    {
        $this->measurements = $measurements;
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
        $this->setCreatedAt($this->measurements->time());
        $this->statement = $this->normalizeStatement($query->sql);
        $this->connection = $query->connectionName;
        $this->processedIn = round($query->time);

        return $this;
    }

    /**
     * Remove redundant spaces
     * Remove newlines
     * Shorten IN (..) clauses
     *
     * @param $query
     *
     * @return string
     */
    protected function normalizeStatement($query)
    {
        $query = preg_replace('!\s+!', ' ', mb_strtolower($query));
        $query = str_replace("\n", "", $query);
        $query = preg_replace('/\sin\s+\((.*)\s?\)/', ' in (...)', $query);

        return $query;
    }
}
