<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendVerificationEmail;
use App\Models\User;
use App\Traits\VerificationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class UserController extends Controller
{
    use VerificationTrait ;

    public function register(StoreUserRequest $request){

        try {

            $validated = $request->validated() ;
            $validated['password'] = Hash::make($validated['password']) ;
            DB::beginTransaction() ;
            $user = User::create($validated) ;
            $code = $this->generateCode();

            // $code = $this->sendCode($user) ;
            $token = $user->createToken('user')->plainTextToken ;

            Queue::push(new SendVerificationEmail($user, $code));

            DB::commit() ;
            return response()->json([
                'message' => 'تم إنشاء الحساب بنجاح يرجى تفعيل الحساب باستخدام الكود المرسل إلى بريدك الإلكتروني.' ,
                'User'=>new UserResource($user ,$token) ,
                'Code'=>$code
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack() ;
            Log::error('User registration failed: ' . $e->getMessage(), [
                'email' => $request->email,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                // 'message'=>$e->getMessage()
                'message'=>'حدث خطأ أثناء إنشاء الحساب يرجى المحاولة مرة أخرى'
            ], 500);
        }

    }

    private function generateCode()
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

}
