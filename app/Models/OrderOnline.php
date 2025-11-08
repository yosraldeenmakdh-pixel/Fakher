<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderOnline extends Model
{

    protected $table = 'order_onlines';

    protected $fillable = [
        'order_number',
        'user_id',
        'branch_id',
        'kitchen_id',
        'total',
        'status',
        'order_date',
        'confirmed_at',
        'delivered_at',
        'special_instructions',
        'customer_name',
        'customer_phone',
        'address' ,
        'confirmed_by' ,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function items()
    {
        return $this->hasMany(OrderOnlineItem::class , 'order_online_id');
    }

    public static function generateOrderNumber()
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));

        return "{$prefix}-{$date}-{$random}";
    }
}
