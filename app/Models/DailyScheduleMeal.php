<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyScheduleMeal extends Model
{
    protected $fillable = [
        'daily_kitchen_schedule_id', 'meal_id', 'meal_type'
    ];

    public function schedule()
    {
        return $this->belongsTo(DailyKitchenSchedule::class, 'daily_kitchen_schedule_id');
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

    public function getMealNameAttribute(): string
    {
        return $this->meal->name ?? 'غير محدد';
    }



     /**
     * الحصول على سعر الوجبة للمؤسسات المجدولة
     */
    public function getScheduledPriceAttribute()
    {
        return InstitutionalMealPrice::getActivePrice($this->meal_id) ?? $this->meal->price;
    }



    public function getPriceInfo(): array
    {
        $scheduledPrice = InstitutionalMealPrice::getActivePrice($this->meal_id);
        $originalPrice = $this->meal->price;
        $finalPrice = $scheduledPrice ?: $originalPrice;

        return [
            'original_price' => $originalPrice,
            'scheduled_price' => $scheduledPrice,
            'final_price' => $finalPrice,
            'has_discount' => $scheduledPrice && $scheduledPrice < $originalPrice,
            'discount_percentage' => $scheduledPrice && $originalPrice > 0
                ? round((($originalPrice - $scheduledPrice) / $originalPrice) * 100, 2)
                : 0,
            'is_scheduled_price' => (bool) $scheduledPrice
        ];
    }

}
