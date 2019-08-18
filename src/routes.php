<?php

/**
 * @codeCoverageIgnore
 */
Route::group(['namespace' => '\Larashed\Agent\Http\Controllers', 'prefix' => 'larashed'], function () {
    Route::get('health-check', 'HealthCheckController@index');
    Route::get('agent/send', 'AgentController@index');
});
