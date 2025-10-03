<?php

namespace App\Traits ;

use App\Mail\CodeMail;
use App\Models\Code;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code as InlineCode;

trait VerificationTrait{

    public function sendCode($user){

            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            Code::create([
                'email'=>$user->email ,
                'code'=>$code
            ]) ;
            Cache::put(
                "verification:{$user->email}",
                $code,
                now()->addMinutes(30)
            );

            Mail::to($user)->send(new CodeMail($code));

            return $code ;

    }
}
