<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('home');
});

Route::get('/add', [App\Http\Controllers\ShortUrlController::class, 'add']);
Route::get('/hash_url', [App\Http\Controllers\ShortUrlController::class, 'get_shortner']);
Route::get('/long_url', [App\Http\Controllers\ShortUrlController::class, 'get_url']);