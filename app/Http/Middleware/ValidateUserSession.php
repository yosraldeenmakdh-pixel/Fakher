<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class ValidateUserSession
{
    public function handle($request, Closure $next)
    {
        // إذا كان هناك جلسة مستخدم نشطة (حتى لو مستخدم محذوف)
        if (Auth::check() || Session::has('login_web_'.sha1(static::class))) {

            try {
                // حالة 1: محاولة جلب المستخدم من Auth (قد يعود null إذا تم حذفه)
                $user = Auth::user();

                // حالة 2: إذا لم نجد مستخدم من Auth، نحاول من الجلسة مباشرة
                if (!$user && Session::has('login_web_'.sha1(static::class))) {
                    $userId = Session::get('login_web_'.sha1(static::class));

                    // تحقق مباشرة من قاعدة البيانات
                    $userExists = DB::table('users')->where('id', $userId)->exists();

                    if (!$userExists) {
                        return $this->forceLogout($request, 'حسابك لم يعد موجوداً');
                    }
                }

                // حالة 3: إذا وجدنا المستخدم، نتحقق من وجوده في DB
                if ($user) {
                    // تحقق مباشرة من قاعدة البيانات (بدون الاعتماد على المودل المخزن)
                    $dbUser = DB::table('users')->where('id', $user->id)->first();

                    if (!$dbUser) {
                        return $this->forceLogout($request, 'حسابك لم يعد موجوداً');
                    }

                    // حالة 4: إذا كان soft deleted
                    if (isset($dbUser->deleted_at) && $dbUser->deleted_at !== null) {
                        return $this->forceLogout($request, 'تم حذف حسابك');
                    }

                    // حالة 5: التحقق من التوكن إذا كان مستخدماً
                    if ($this->isUsingTokens() && !$this->validateTokenFromDB($user->id)) {
                        return $this->forceLogout($request, 'جلسة الدخول منتهية الصلاحية');
                    }
                }

            } catch (\Exception $e) {
                // في حالة أي خطأ، نسجل الخروج احترازياً
                Log::error('Session validation error: ' . $e->getMessage());
                return $this->forceLogout($request, 'حدث خطأ في الجلسة');
            }
        }

        return $next($request);
    }

    /**
     * التحقق من وجود التوكن في قاعدة البيانات
     */
    private function validateTokenFromDB($userId)
    {
        // الطريقة 1: للجلسات العادية (إذا كنت تخزن التوكن في DB)
        $tokenColumn = config('auth.guards.web.provider', 'users') == 'users' ? 'remember_token' : null;

        if ($tokenColumn) {
            $user = DB::table('users')
                ->where('id', $userId)
                ->whereNotNull($tokenColumn)
                ->exists();

            return $user;
        }

        // الطريقة 2: إذا كنت تستخدم Sanctum
        if (class_exists('Laravel\Sanctum\PersonalAccessToken')) {
            $hasToken = DB::table('personal_access_tokens')
                ->where('tokenable_id', $userId)
                ->where('tokenable_type', User::class)
                ->where('expires_at', '>', now())
                ->exists();

            return $hasToken;
        }

        // الطريقة 3: إذا كنت تستخدم Passport
        if (class_exists('Laravel\Passport\Token')) {
            $hasToken = DB::table('oauth_access_tokens')
                ->where('user_id', $userId)
                ->where('revoked', 0)
                ->where('expires_at', '>', now())
                ->exists();

            return $hasToken;
        }

        return true; // إذا لم تستخدم نظام توكن
    }

    /**
     * التحقق مما إذا كان النظام يستخدم التوكنات
     */
    private function isUsingTokens()
    {
        return class_exists('Laravel\Sanctum\PersonalAccessToken') ||
               class_exists('Laravel\Passport\Token');
    }

    /**
     * إجبار تسجيل الخروج
     */
    private function forceLogout($request, $message = null)
    {
        // استخدام Guard المناسب
        $guards = array_keys(config('auth.guards'));

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // مسح جميع الكوكيز
        $cookieNames = ['remember_web', 'XSRF-TOKEN', 'laravel_session'];
        foreach ($cookieNames as $cookieName) {
            Cookie::queue(Cookie::forget($cookieName));
        }

        if ($message) {
            Session::flash('error', $message);
        }

        return redirect('/');
    }
}
