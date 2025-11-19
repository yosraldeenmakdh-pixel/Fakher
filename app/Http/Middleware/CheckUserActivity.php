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
        $inactiveDays = Carbon::now()->diffInDays($lastActivity) ;
        $inactiveDays *= -1 ;


        if ($inactiveDays >= 7) {

            $user->tokens()->delete();

            return response()->json([
                'success' => false,
                'message' => 'تم تسجيل الخروج تلقائياً بسبب عدم النشاط لمدة أسبوع',
                'logout_reason' => 'inactivity'
            ], 401);
        }

        $inactiveHours = Carbon::now()->diffInHours($user->last_activity_at) ;
        $inactiveHours *= -1 ;

        $shouldUpdate = !$user->last_activity_at || $inactiveHours >= 1 ;

        if ($shouldUpdate) {
            $user->last_activity_at = now() ;
            $user->save() ;
        }

        return $next($request);

    }
}
