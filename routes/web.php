<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('home');
});

Route::post('/create_shortner', [App\Http\Controllers\ShortUrlController::class, 'add_shortner']);
Route::get('/rckt/', [App\Http\Controllers\ShortUrlController::class, 'get_shortner']);
// Route::post('/', [App\Http\Controllers\ShortUrlController::class, 'add_shortner']);
// Route::post('/', [App\Http\Controllers\ShortUrlController::class, 'add_shortner']);
// Route::post('/', [App\Http\Controllers\ShortUrlController::class, 'add_shortner']);
