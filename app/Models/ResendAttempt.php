<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResendAttempt extends Model
{
    protected $fillable = ['email', 'attempts', 'last_attempt_at'];
}
