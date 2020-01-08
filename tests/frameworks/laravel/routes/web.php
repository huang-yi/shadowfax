<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Hello Shadowfax! (Laravel)';
});

Route::get('blocking', function () {
    $before = request('input');

    Swoole\Coroutine::sleep(0.1);

    $after = request('input');

    return $before.'->'.$after;
});
