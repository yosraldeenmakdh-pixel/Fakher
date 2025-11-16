<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledInstitutionOrderMeal extends Model
{
    protected $fillable = [
        'order_id',
        'daily_schedule_meal_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {

            // if (!$model->validateUniqueOrder()) {
            //     throw new \Exception('لا يمكن إنشاء طلب جديد في نفس اليوم للمؤسسة والفرع والمطبخ نفسه. يرجى تعديل الطلب الحالي أو اختيار تاريخ آخر.');
            // }

            $model->total_price = $model->quantity * $model->unit_price;
        });
    }

    public function order()
    {
        return $this->belongsTo(ScheduledInstitutionOrder::class, 'order_id');
    }

    public function scheduleMeal()
    {
        return $this->belongsTo(DailyScheduleMeal::class, 'daily_schedule_meal_id');
    }

    public function meal()
    {
        return $this->scheduleMeal->meal();
    }

    public function getMealNameAttribute(): string
    {
        return $this->scheduleMeal->meal->name ?? 'غير محدد';
    }

    public function getMealTypeAttribute(): string
    {
        return $this->scheduleMeal->meal_type ?? 'غير محدد';
    }
}
