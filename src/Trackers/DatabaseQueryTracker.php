<?php

namespace Larashed\Agent\Trackers;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Larashed\Agent\System\Measurements;
use Larashed\Agent\Trackers\Database\Query;
use Larashed\Agent\Trackers\Database\QueryExcluder;

/**
 * Class DatabaseQueryTracker
 *
 * @package Larashed\Agent\Trackers
 */
class DatabaseQueryTracker implements TrackerInterface
{
    /**
     * @var Measurements
     */
    protected $measurements;

    /**
     * @var QueryExcluder
     */
    protected $excluder;

    /**
     * @var array
     */
    protected $queries;

    /**
     * DatabaseQueryTracker constructor.
     *
     * @param Measurements  $measurements
     * @param QueryExcluder $excluder
     */
    public function __construct(Measurements $measurements, QueryExcluder $excluder)
    {
        $this->measurements = $measurements;
        $this->excluder = $excluder;
    }

    /**
     * Bind database query events
     */
    public function bind()
    {
        DB::listen($this->onQueryCallback());
    }

    /**
     * Gather query data
     *
     * @return array
     */
    public function gather()
    {
        if (empty($this->queries)) {
            return [];
        }

        $queries = collect($this->queries)->map(function (Query $query) {
            return $query->toArray();
        });

        return $this->groupQueries($queries->toArray());
    }

    /**
     * Cleanup query data
     *
     * @return $this
     */
    public function cleanup()
    {
        $this->queries = [];

        return $this;
    }

    /**
     * @return Closure
     */
    protected function onQueryCallback()
    {
        return function (QueryExecuted $query) {
            if (!$this->excluder->shouldExclude($query)) {
                $this->queries[] = (new Query($this->measurements, $query));
            }
        };
    }

    /**
     * Indexes queries so that we can save some bandwidth
     * and don't need to worry about processing large payloads
     *
     * @param $queries
     *
     * @return array
     */
    protected function groupQueries($queries)
    {
        $groups = [];

        foreach ($queries as $key => $query) {
            $index = array_search($query['query'], $groups);
            if ($index !== false) {
                $queries[$key]['query'] = $index;
                continue;
            }

            $groups[] = $query['query'];
            $queries[$key]['query'] = count($groups) - 1;
        }

        return [
            'groups'  => $groups,
            'queries' => $queries
        ];
    }
}
