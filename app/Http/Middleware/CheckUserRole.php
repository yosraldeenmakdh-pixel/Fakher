<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->roles->isEmpty()) {
            Auth::logout();

            $key = 'role_access:' . ($request->ip() ?? 'unknown');

            // التحقق من عدد المحاولات
            if (RateLimiter::tooManyAttempts($key, 3)) {
                $seconds = RateLimiter::availableIn($key);
                $minutes = ceil($seconds / 10);

                return response()->view('errors.too-many-requests', [
                    'minutes' => $minutes,
                    'seconds' => $seconds
                ], 429);
            }

            // زيادة العداد
            RateLimiter::hit($key, 300); // 5 دقائق

            $attempts = RateLimiter::attempts($key);
            $remaining = 3 - $attempts;

            return response()->view('errors.unauthorized', [
                'remaining' => $remaining,
                'attempts' => $attempts
            ], 403);
        }

        return $next($request);
    }
}
