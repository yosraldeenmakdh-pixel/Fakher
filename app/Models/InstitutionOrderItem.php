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
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->quantity && $model->unit_price) {
                $model->total_price = $model->quantity * $model->unit_price;
            }
        });

        static::saved(function ($model) {
            if ($model->order) {
                $model->order->update([
                    'total_amount' => $model->order->orderItems()->sum('total_price')
                ]);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty(['quantity', 'unit_price'])) {
                $model->total_price = $model->quantity * $model->unit_price;
            }
        });
    }
}
