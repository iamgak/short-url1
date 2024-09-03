<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureTokenIsValid;


// Route::get('/', function () {
//     return view('home');
// });

Route::get('/', [App\Http\Controllers\ShortUrlController::class, 'home']);
Route::get('/login', [App\Http\Controllers\UserController::class, 'login']);
Route::middleware([EnsureTokenIsValid::class])->group(function () {
    Route::get('/url/{shortner}', [App\Http\Controllers\ShortUrlController::class, 'redirect']);
    Route::post('/add', [App\Http\Controllers\ShortUrlController::class, 'add']);
    Route::get('/logout', [App\Http\Controllers\UserController::class, 'logout']);
});
Route::get('/register', [App\Http\Controllers\UserController::class, 'register']);
Route::get('/account_verification/{token}', [App\Http\Controllers\UserController::class, 'email_verification'])->middleware(EnsureTokenIsValid::class);
// Route::methodNotAllowed(function () {
//     return view('errors.method-not-allowed');
// });
// Route::fallback(function () {
//     return "view('errors.404')";
// });
