<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('home');
});

Route::post('/add', [App\Http\Controllers\ShortUrlController::class, 'add_shortner']);
Route::get('/hash_url', [App\Http\Controllers\ShortUrlController::class, 'get_shortner']);
Route::get('/long_url', [App\Http\Controllers\ShortUrlController::class, 'get_url']);