<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderOnlineItem extends Model
{
    protected $fillable = [
        'order_online_id', 'meal_id', 'quantity',
        'unit_price', 'total_price'
    ];

    public function order_online()
    {
        return $this->belongsTo(OrderOnline::class ,'order_online_id');
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }
}
