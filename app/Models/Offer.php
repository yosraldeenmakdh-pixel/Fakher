<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'name',
        'description',
        'discount_value',
        'is_active',
        'image' ,
        'meal_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        // 'discount_value' => 'decimal:2',
    ];

}
