<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'table_id',
        'user_id',
        'customer_name',
        'customer_phone',
        'arrival_time',
        'departure_time',
        'actual_meal_end',
        'status',
        'notes',
        'guests_count'
    ];

    protected $casts = [
        'arrival_time' => 'datetime',
        'departure_time' => 'datetime',
    ];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
