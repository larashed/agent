<?php

namespace Larashed\Agent\Trackers\Database;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Str;

/**
 * Class QueryExcluder
 *
 * @package Larashed\Agent\Trackers\Database
 */
class QueryExcluder
{
    /**
     * @var QueryExcluderConfig
     */
    protected $config;

    /**
     * QueryExcluder constructor.
     *
     * @param QueryExcluderConfig $config
     */
    public function __construct(QueryExcluderConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param QueryExecuted $query
     *
     * @return bool
     */
    public function shouldExclude(QueryExecuted $query)
    {
        return $this->isQueueConnectionQuery($query);
    }

    /**
     * @param QueryExecuted $query
     *
     * @return bool
     */
    protected function isQueueConnectionQuery(QueryExecuted $query)
    {
        $match = [
            'into `' . $this->config->getFailedJobTable() . '`',
        ];

        // apply this to the database driver only
        // someone may have a jobs table for other uses
        if ($this->config->getQueueDriver() === 'database') {
            $match = array_merge($match, [
                'from `' . $this->config->getJobTable() . '`',
                'update `' . $this->config->getJobTable() . '`',
                'into `' . $this->config->getJobTable() . '`'
            ]);
        }

        return Str::contains(strtolower($query->sql), $match);
    }
}
