<?php

namespace Larashed\Agent\Storage;

use Carbon\Carbon;
use Illuminate\Database\ConnectionResolverInterface;

/**
 * Class DatabaseStorage
 *
 * @package Larashed\Agent\Storage
 */
class DatabaseStorage implements AgentStorageInterface
{
    /**
     * @var ConnectionResolverInterface
     */
    protected $resolver;

    /**
     * @var
     */
    protected $connection;

    /**
     * @var string
     */
    protected $table;

    /**
     * DatabaseStorage constructor.
     *
     * @param ConnectionResolverInterface $resolver
     * @param                             $connection
     * @param                             $table
     */
    public function __construct(ConnectionResolverInterface $resolver, $connection, $table)
    {
        $this->resolver = $resolver;
        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * @param array|\JsonSerializable $record
     */
    public function addRecord($record)
    {
        $this->getTable()->insert(['payload' => json_encode($record), 'created_at' => Carbon::now()->timestamp]);
    }

    /**
     * @param int $limit
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRecords($limit = 1000)
    {
        return $this->getTable()->limit($limit)->get()->pluck('payload', 'id');
    }

    /**
     * @param $identifiers
     */
    public function remove($identifiers)
    {
        $identifiers = (array) $identifiers;

        $this->getTable()->whereIn('id', $identifiers)->delete();
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getTable()
    {
        return $this->resolver->connection($this->connection)->table($this->table);
    }
}
