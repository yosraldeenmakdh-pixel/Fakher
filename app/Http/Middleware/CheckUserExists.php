<?php
// app/Http/Middleware/CheckUserExists.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class CheckUserExists
{
    public function handle($request, Closure $next)
    {
        // إذا كان هناك أي إشارة لجلسة مستخدم (حتى لو انكسرت)
        if ($this->hasAnyUserSession($request)) {

            // جلب ID المستخدم من أي مكان ممكن في الجلسة
            $userId = $this->getUserIdFromSession($request);

            // إذا وجدنا ID مستخدم، نتحقق من وجوده
            if ($userId) {
                $userExists = DB::table('users')->where('id', $userId)->exists();

                // إذا المستخدم غير موجود - ننهي الجلسة فوراً
                if (!$userExists) {
                    return $this->forceGuestMode($request);
                }

                // تحقق إضافي: إذا كان المستخدم soft deleted
                $user = DB::table('users')->where('id', $userId)->first();
                if ($user && !empty($user->deleted_at)) {
                    return $this->forceGuestMode($request);
                }
            } else {
                // إذا لم نجد userId في الجلسة ولكن الجلسة تعتقد أن هناك مستخدم
                return $this->forceGuestMode($request);
            }
        }

        return $next($request);
    }

    /**
     * التحقق من وجود أي إشارة لجلسة مستخدم
     */
    private function hasAnyUserSession($request)
    {
        return Auth::check() ||
               Session::has('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d') ||
               $request->session()->has('password_hash_sanctum') ||
               $request->session()->has('password_hash_web');
    }

    /**
     * جلب ID المستخدم من أي مكان في النظام
     */
    private function getUserIdFromSession($request)
    {
        // المحاولة 1: من Auth مباشرة
        if (Auth::check()) {
            return Auth::id();
        }

        // المحاولة 2: من الجلسة مباشرة (لـ Laravel 7+)
        $sessionKey = 'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d';
        if (Session::has($sessionKey)) {
            return Session::get($sessionKey);
        }

        // المحاولة 3: من الجلسة القديمة
        if ($request->session()->has('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')) {
            return $request->session()->get('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d');
        }

        return null;
    }

    /**
     * تحويل الزائر إلى وضع الضيف فوراً
     */
    private function forceGuestMode($request)
    {
        // 1. تسجيل الخروج من جميع Guards
        $guards = ['web', 'api'];
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }

        // 2. إلغاء الجلسة بالكامل
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 3. مسح جميع الكوكيز المتعلقة بالمصادقة
        $cookies = $request->cookies->all();
        foreach ($cookies as $name => $value) {
            if (str_contains($name, 'remember') ||
                str_contains($name, 'token') ||
                str_contains($name, 'session')) {
                Cookie::queue(Cookie::forget($name));
            }
        }

        // 4. مسح كوكيز Laravel الأساسية
        Cookie::queue(Cookie::forget('laravel_session'));
        Cookie::queue(Cookie::forget('XSRF-TOKEN'));

        // 5. إعادة التوجيه لنفس الصفحة (ستكون الآن زائراً)
        return redirect($request->fullUrl());
    }
}
