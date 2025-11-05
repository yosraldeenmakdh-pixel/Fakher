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
                'ID' => $this->user->id,
                'Name'=>$this->user->name ,
                'Email'=>$this->user->email ,
                'phone' => $this->user->phone,
                'address' => $this->user->address,
                'image' => $this->user->image ? asset('storage/' . $this->user->image) : null,
                'Email'=>$this->user->email ,
                'Created_at' => $this->user->created_at->format('Y-m-d H:i:s'),
                'Updated_at' => $this->user->updated_at->format('Y-m-d H:i:s'),
            ] ,
            'Token' => $this->token
        ] ;
    }
}
