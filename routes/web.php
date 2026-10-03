<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/docs/swagger');
});

Route::get('/docs/swagger', function () {
    return view('swagger');
});

Route::get('/swagger', function () {
    return redirect('/docs/swagger');
});

Route::get('/docs', function () {
    return redirect('/docs/api');
});
