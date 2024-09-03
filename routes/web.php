<?php

use Illuminate\Support\Facades\Route;


// Route::get('/', function () {
//     return view('home');
// });

Route::get('/', [App\Http\Controllers\ShortUrlController::class, 'home']);
Route::get('/add', [App\Http\Controllers\ShortUrlController::class, 'add']);
Route::get('/{shortner}', [App\Http\Controllers\ShortUrlController::class, 'redirect']);