<?php

namespace Larashed\Agent\Trackers;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Events\QueryExecuted;
use Larashed\Agent\Trackers\Database\Query;
use Larashed\Agent\Trackers\Database\QueryExcluder;
use Larashed\Agent\System\Measurements;

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
        $queries = collect($this->queries)->map(function (Query $query) {
            return $query->toArray();
        });

        return $queries->toArray();
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
}
