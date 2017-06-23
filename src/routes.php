<?php

Route::get('health-check', function () {
    return response('OK', 200);
});