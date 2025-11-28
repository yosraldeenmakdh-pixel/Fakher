<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmergencyItem extends Model
{
    protected $fillable = [
        'emergency_id',
        'meal_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * العلاقة مع الطلب الطارئ
     */
    public function emergency()
    {
        return $this->belongsTo(Emergency::class);
    }

    /**
     * العلاقة مع الوجبة
     */
    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

}
