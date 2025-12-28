<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchMeal extends Model
{
    protected $table = 'branch_meal';

    protected $fillable = [
        'branch_id',
        'meal_id',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];


    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }
}
