<?php

Route::get('larashed/health-check', function () {
    return response('OK', 200);
});
