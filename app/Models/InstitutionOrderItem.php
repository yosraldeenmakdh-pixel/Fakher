<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitutionOrderItem extends Model
{
    protected $fillable = [
        'institution_order_id',
        'meal_id',
        'quantity',
        'unit_price',
        'total_price'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * العلاقة مع الطلب
     */
    public function order()
    {
        return $this->belongsTo(InstitutionOrder::class, 'institution_order_id');
    }

    /**
     * العلاقة مع الوجبة
     */
    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

    /**
     * حساب السعر الإجمالي تلقائياً
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->total_price = $model->quantity * $model->unit_price;
        });
    }
}
