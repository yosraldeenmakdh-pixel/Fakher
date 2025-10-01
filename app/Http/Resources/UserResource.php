<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public $user,$token ;

    public function __construct($user ,$token) {
        $this->user = $user ;
        $this->token = $token ;
    }
    public function toArray(Request $request): array
    {
        return[
            'User' => [
                'Name'=>$this->user->name ,
                'Email'=>$this->user->email ,
                'Date'=>$this->user->created_at->format('Y-m-d') ,
            ] ,
            'Token' => $this->token
        ] ;
    }
}
