<?php

// namespace App\Http\Middleware;

// use Closure;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\RateLimiter;

// class RateLimiter
// {
//     public function handle(Request $request, Closure $next)
//     {
//         $key = $request->fingerprint();

//         $maxAttempts = 100;
//         $decayMinutes = 1;

//         if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
//             return response()->json([
//                 'message' => 'Too many requests',
//             ], 429);
//         }

//         RateLimiter::hit($key, $decayMinutes);

//         return $next($request);
//     }
// }
