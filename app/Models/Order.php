<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'kitchen_id',
        'name',
        'total',
        'special_instructions',
        'status',
        'delivered_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
