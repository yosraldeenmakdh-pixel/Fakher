<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckCodeRequest;
use App\Http\Requests\CheckRestCodeRequest;
use App\Http\Requests\EmailRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendRestCodeEmail;
use App\Jobs\SendVerificationEmail;
use App\Mail\RestCodeMail;
use App\Models\Code;
use App\Models\ResendAttempt;
use App\Models\RestCode;
use App\Models\User;
use App\Traits\VerificationTrait;
use Carbon\Carbon;
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





    public function checkCode(CheckCodeRequest $request){

        try {

            DB::beginTransaction();

            $validated = $request->validated() ;

            $code = Code::where('code',$validated['code'])->first() ;

            if (!$code) {
                return response()->json([
                    'message' => 'كود التحقق غير صحيح'
                ], 422);
            }

            $user = User::where('email',$code->email)->first() ;

            if($user->verified_at != null)
                return response()->json([
                    'message'=>'البريد الإلكتروني مفعل مسبقاً'
                ], 409);

            if($code->created_at->addMinute(30) < now()){
                return response()->json([
                    'message'=>'انتهت صلاحية كود التحقق. يرجى طلب كود جديد']
                , 410);
            }

            $user->email_verified_at = now() ;
            $user->save() ;

            $code->delete() ;

            DB::commit() ;
            return response()->json([
                'message'=>'تم تفعيل حسابك بنجاح'
            ], 200);


        } catch (\Exception $e) {
            DB::rollBack() ;
            Log::error('Check Code operation failed: ' . $e->getMessage(), [
                'email' => $user->email,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message'=>'حدث خطأ أثناء يرجى المحاولة مرة أخرى'
            ], 500);
        }
    }





    public function resendCode(Request $request)
    {
        try {

            DB::beginTransaction() ;
            $request->validate([
                'email' => 'required|email|exists:users,email'
            ]);

            $email = $request->email;
            $now = now();
            $waitTime = 60;

            $resendAttempt = ResendAttempt::where('email', $email)->first();

            if ($resendAttempt) {

                if ($resendAttempt->attempts >= 3) {
                    $lastAttempt = Carbon::parse($resendAttempt->last_attempt_at) ;
                    $diffInMinutes = $lastAttempt->diffInMinutes($now);

                    if ($diffInMinutes < $waitTime) {
                        $remainingMinutes = ceil($waitTime - $diffInMinutes) ;
                        return response()->json([
                            'message' => "لقد استنفذت عدد المحاولات. يرجى الانتظار {$remainingMinutes} دقيقة قبل المحاولة مرة أخرى."
                        ], 429);
                    }

                    $resendAttempt->update([
                        'attempts' => 1,
                        'last_attempt_at' => $now
                    ]);
                } else {
                    $resendAttempt->update([
                        'attempts' => $resendAttempt->attempts + 1,
                        'last_attempt_at' => $now
                    ]);
                }
            } else {
                // إنشاء سجل جديد إذا لم يكن موجود
                ResendAttempt::create([
                    'email' => $email,
                    'attempts' => 1,
                    'last_attempt_at' => $now
                ]);
            }

            // إعادة إرسال الكود
            $user = User::where('email', $email)->first();
            $code = $this->generateCode();

            Code::where('email', $email)->delete();

            Queue::push(new SendVerificationEmail($user, $code));

            DB::commit() ;

            return response()->json([
                'message' => 'تم إرسال الكود بنجاح'
            ], 200);


        } catch (\Exception $e) {
            DB::rollBack() ;
            Log::error('Failed to resend code' . $e->getMessage(), [
                'email' => $request->email
            ]);
            return response()->json([
                'message'=>'حدث خطأ أثناء يرجى المحاولة مرة أخرى'
            ], 500);
        }
    }








    public function login(LoginUserRequest $request){

        try {
            $validated = $request->validated() ;
            $user = User::where('email',$validated['email'])->first() ;

            if (!$user) {
                return response()->json([
                    'message' => 'البريد الإلكتروني غير مسجل في النظام'
                ], 404);
            }

            if(!Hash::check($validated['password'] ,$user->password))
                return response()->json([
                    'message'=>'كلمة المرور غير صحيحة'
                ], 401);

            if ($user->email_verified_at == null) {
                return response()->json([
                    'message'=>'يجب تفعيل البريد الإلكتروني قبل تسجيل الدخول.'
                ], 403);
            }

            $token = $user->createToken('user')->plainTextToken ;

            return response()->json([
                'user' => new UserResource($user,$token),
                'message' => 'تم تسجيل الدخول بنجاح'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), [
                'email' => $user->email,

            ]);
            return response()->json([
                'message'=>'حدث خطأ أثناء يرجى المحاولة مرة أخرى'
            ], 500);
        }
    }




    public function forgot_password(EmailRequest $request){

        try {
            DB::beginTransaction() ;
            $validated = $request->validated() ;
            $user = User::where('email',$validated['email'])->first() ;

            if (!$user) {
                return response()->json([
                    'message' => 'البريد الإلكتروني غير مسجل في النظام'
                ], 404);
            }

            $code = $this->generateCode();

            RestCode::where('email', $validated['email'])->delete();

            Queue::push(new SendRestCodeEmail($user, $code));

            DB::commit() ;
            return response()->json([
                'message'=>'تم إرسال رمز التحقق إلى بريدك الإلكتروني بنجاح'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack() ;

            Log::error('Failed to reset the password', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message'=>'حدث خطأ أثناء عملية إعادة التعيين. يرجى المحاولة مرة أخرى'
            ], 500);
        }
    }



    public function checkRestCode(CheckRestCodeRequest $request){

        try {
            DB::beginTransaction() ;
            $validated = $request->validated() ;

            $restcode = RestCode::where('code',$validated['code'])->first() ;

            if (!$restcode) {
                return response()->json([
                    'message' => 'الكود غير صحيح.'
                ], 404);
            }

            if($restcode->created_at->addMinutes(30) < now()){
                $restcode->delete() ;
                return response()->json([
                    'message'=>'انتهت صلاحية كود التحقق. يرجى طلب كود جديد'
                ], 410);
            }

            $reset_token = bin2hex(random_bytes(30));
            $restcode->update(['reset_token' => $reset_token]) ;

            DB::commit() ;
            return response()->json([
                'message'=>'تم التحقق من الكود' ,
                'reset_token'=>$reset_token
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack() ;
            Log::error('Check Rest Code operation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message'=>'حدث خطأ أثناء يرجى المحاولة مرة أخرى'
            ], 500);

        }
    }




    public function resendResetCode(Request $request)
    {
        try {

            DB::beginTransaction() ;
            $request->validate([
                'email' => 'required|email|exists:users,email'
            ]);

            $email = $request->email;
            $now = now();
            $waitTime = 60;

            $resendAttempt = ResendAttempt::where('email', $email)->first();

            if ($resendAttempt) {

                if ($resendAttempt->attempts >= 3) {
                    $lastAttempt = Carbon::parse($resendAttempt->last_attempt_at) ;
                    $diffInMinutes = $lastAttempt->diffInMinutes($now);

                    if ($diffInMinutes < $waitTime) {
                        $remainingMinutes = ceil($waitTime - $diffInMinutes) ;
                        return response()->json([
                            'message' => "لقد استنفذت عدد المحاولات. يرجى الانتظار {$remainingMinutes} دقيقة قبل المحاولة مرة أخرى."
                        ], 429);
                    }

                    $resendAttempt->update([
                        'attempts' => 1,
                        'last_attempt_at' => $now
                    ]);
                } else {
                    $resendAttempt->update([
                        'attempts' => $resendAttempt->attempts + 1,
                        'last_attempt_at' => $now
                    ]);
                }
            } else {

            ResendAttempt::create([
                    'email' => $email,
                    'attempts' => 1,
                    'last_attempt_at' => $now
                ]);
            }

            $user = User::where('email', $email)->first();
            $code = $this->generateCode();

            RestCode::where('email', $email)->delete();

            Queue::push(new SendRestCodeEmail($user, $code));

            DB::commit() ;

            return response()->json([
                'message' => 'تم إرسال الكود بنجاح'
            ], 200);


        } catch (\Exception $e) {
            DB::rollBack() ;
            Log::error('Failed to resend code' . $e->getMessage(), [
                'email' => $request->email
            ]);
            return response()->json([
                'message'=>'حدث خطأ أثناء يرجى المحاولة مرة أخرى'
            ], 500);
        }
    }






    public function resetPassword(ResetPasswordRequest $request){

        try {
            DB::beginTransaction() ;
            $validated = $request->validated() ;

            $restcode = RestCode::where('reset_token', $validated['reset_token'])->first();

            if (!$restcode) {
                return response()->json([
                    'message' => 'التوكن غير صحيح.'
                ], 404);
            }

            if($restcode->created_at->addMinutes(30) < now()){
                $restcode->delete() ;
                return response()->json([
                    'message'=>'انتهت صلاحية كود التحقق. يرجى طلب كود جديد'
                ], 410);
            }

            $user = User::where('email',$restcode['email'])->first() ;

            $user->update(['password'=>Hash::make($validated['password'])]) ;

            RestCode::where('email', $restcode['email'])->delete();

            DB::commit() ;

            return response()->json([
                'message'=>'تمت إعادة تعيين كلمة المرور بنجاح'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack() ;
            Log::error('Password reset operation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'reset_token' => $request->reset_token ?? 'none',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return response()->json([
                'message'=>'حدث خطأ أثناء يرجى المحاولة مرة أخرى'
            ], 500);

        }
    }



}
