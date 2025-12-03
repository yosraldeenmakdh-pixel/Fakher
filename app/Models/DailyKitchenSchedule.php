<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

// use League\Config\Exception\ValidationException;

class DailyKitchenSchedule extends Model
{
    protected $fillable = [
        'kitchen_id', 'schedule_date'
    ];

    protected $casts = [
        'schedule_date' => 'date',
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function scheduledMeals()
    {
        return $this->hasMany(DailyScheduleMeal::class);
    }

    public function breakfastMeals()
    {
        return $this->hasMany(DailyScheduleMeal::class)
                    ->where('meal_type', 'breakfast')
                    ->with('meal');
    }

    /**
     * الحصول على وجبات الغداء فقط
     */
    public function lunchMeals()
    {
        return $this->hasMany(DailyScheduleMeal::class)
                    ->where('meal_type', 'lunch')
                    ->with('meal');
    }

    /**
     * الحصول على وجبات العشاء فقط
     */
    public function dinnerMeals()
    {
        return $this->hasMany(DailyScheduleMeal::class)
                    ->where('meal_type', 'dinner')
                    ->with('meal');
    }

    public function getMealsByType(string $mealType)
    {
        return $this->scheduledMeals()
                    ->where('meal_type', $mealType)
                    ->with('meal')
                    ->get();
    }

    /**
     * التحقق من وجود جدولة لهذا المطبخ في تاريخ معين
     */
    public static function getSchedule($kitchenId, $date)
    {
        return static::where('kitchen_id', $kitchenId)
                    ->where('schedule_date', $date)
                    ->first();
    }



    public function calculateDailyCost($breakfastPersons, $lunchPersons, $dinnerPersons): float
    {
        $total = 0;

        $total += $this->calculateMealTypeCost($this->breakfastMeals, $breakfastPersons);
        $total += $this->calculateMealTypeCost($this->lunchMeals, $lunchPersons);
        $total += $this->calculateMealTypeCost($this->dinnerMeals, $dinnerPersons);

        return $total;
    }

    /**
     * حساب تكلفة نوع وجبة معين
     */
    protected function calculateMealTypeCost($meals, $persons): float
    {
        if ($meals->isEmpty() || $persons === 0) {
            return 0;
        }

        $totalCost = 0;
        $mealCount = $meals->count();

        foreach ($meals as $meal) {
            $price = $meal->scheduled_price;
            $totalCost += $price * ($persons / $mealCount);
        }

        return $totalCost;
    }


    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $exists = self::where('kitchen_id', $model->kitchen_id)
                ->whereDate('schedule_date', $model->schedule_date)
                ->when($model->exists, fn($q) => $q->where('id', '!=', $model->id))
                ->exists();

            if ($exists) {
                // هذا سيؤدي إلى خطأ Validation بدلاً من QueryException
                throw ValidationException::withMessages([
                    'schedule_date' => 'هذا التاريخ مجدول بالفعل'
                ]);
            }
        });
    }

    public static function getScheduledDates($kitchenId)
    {
        return self::where('kitchen_id', $kitchenId)
            ->whereDate('schedule_date', '>=', now())
            ->pluck('schedule_date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->toArray();
    }




}
