<?php

Route::group(['namespace' => '\Larashed\Agent\Http\Controllers'], function() {
    Route::get('larashed/health-check', 'LarashedController@healthCheck');
});
