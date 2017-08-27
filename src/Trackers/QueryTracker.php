<?php

namespace Larashed\Agent\Trackers;

use DB;
use Carbon\Carbon;
use Illuminate\Database\Events\QueryExecuted;

/**
 * Class QueryTracker
 *
 * @package Larashed\Agent\Trackers
 */
class QueryTracker extends BaseTracker
{
    /**
     * @return $this
     */
    public function bind()
    {
        DB::listen(function (QueryExecuted $query) {
            if ($this->isQueueConnectionQuery($query)) {
                return;
            }

            $data = [
                'connection'   => $query->connectionName,
                'query'        => $query->sql,
                'processed_in' => $query->time,
                'created_at'   => Carbon::now('UTC')->format('c')
            ];

            $this->agent->getCollector()->addQuery($data);
        });

        return $this;
    }

    /**
     * @param QueryExecuted $query
     *
     * @return bool
     */
    protected function isQueueConnectionQuery(QueryExecuted $query)
    {
        $connection = config('queue.default');

        if ($connection !== 'database') {
            return false;
        }

        $jobTable = config('queue.connections.' . $connection . '.table');
        $failedJobTable = config('queue.failed.table');

        $match = [
            'from `' . $jobTable . '`',
            'update `' . $jobTable . '`',
            'into `' . $jobTable . '`',
            'into `' . $failedJobTable . '`',
        ];

        return str_contains($query->sql, $match);
    }
}
