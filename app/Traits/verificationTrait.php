<?php

namespace App\Traits ;

use App\Mail\CodeMail;
use App\Models\Code;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code as InlineCode;

trait VerificationTrait{

    public function sendCode($user){
        try {
            DB::beginTransaction() ;
            $code = mt_rand(100000,999999) ;
            Code::create([
                'email'=>$user->email ,
                'code'=>$code
            ]) ;
            Mail::to($user)->send(new CodeMail($code));
            DB::commit() ;
            return $code ;

        } catch (\Exception $e) {
            DB::rollBack() ;
            return response()->json([
                'message'=>$e->getMessage()
            ],404);
        }
    }
}
