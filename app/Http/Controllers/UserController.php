<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\VerificationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use VerificationTrait ;

    public function register(StoreUserRequest $request){

        try {

            $validated = $request->validated() ;
            $validated['password'] = Hash::make($validated['password']) ;
            DB::beginTransaction() ;
            $user = User::create($validated) ;
            $code = $this->sendCode($user) ;
            $token = $user->createToken('user')->plainTextToken ;
            DB::commit() ;
            return response()->json([
                'User'=>new UserResource($user ,$token) ,
                'Code'=>$code
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack() ;
            return response()->json([
                'message'=>$e->getMessage()
            ], 200);
        }

    }

}
