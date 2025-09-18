<?php
// routes/web.php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return [
        'status' => 'OK',
        'departments' => DB::table('departments')->get(),
        'users_count' => DB::table('users')->count()
    ];
});
