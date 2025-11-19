<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActivity
{

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if(!$user){
            return response()->json('user is not found');
        }

        $lastActivity = $user->last_activity_at ?? $user->created_at;
        $inactiveDays = Carbon::now()->diffInMinutes($lastActivity) ;
        $inactiveDays *= -1 ;

        // echo $inactiveDays ;

        if ($inactiveDays >= 1) {

            $user->tokens()->delete();

            return response()->json([
                'success' => false,
                'message' => 'تم تسجيل الخروج تلقائياً بسبب عدم النشاط لمدة أسبوع',
                'logout_reason' => 'inactivity'
            ], 401);
        }

        // تحديث وقت النشاط كل 10 دقائق لتقليل الضغط على قاعدة البيانات
        $shouldUpdate = !$user->last_activity_at || Carbon::now()->diffInHours($user->last_activity_at) >= 1;

        if ($shouldUpdate) {
            $user->last_activity_at = now() ;
            $user->save() ;
        }

        return $next($request);

    }
}
