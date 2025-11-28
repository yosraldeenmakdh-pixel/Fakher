<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScheduledInstitutionOrder extends Model
{
    protected $fillable = [
        'institution_id',
        'branch_id' ,
        'kitchen_id' ,
        'order_date',
        'breakfast_persons',
        'lunch_persons',
        'dinner_persons',
        'total_amount',

        'breakfast_meals_count',
        'lunch_meals_count',
        'dinner_meals_count',
        'breakfast_amount',
        'lunch_amount',
        'dinner_amount',

        'status',
        'special_instructions',
        'confirmed_at',
        'delivered_at'
    ];

    protected $casts = [
        'order_date' => 'date',
        'confirmed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'breakfast_persons' => 'integer',
        'lunch_persons' => 'integer',
        'dinner_persons' => 'integer',
        'breakfast_meals_count' => 'integer',
        'lunch_meals_count' => 'integer',
        'dinner_meals_count' => 'integer',
        'breakfast_amount' => 'decimal:2',
        'lunch_amount' => 'decimal:2',
        'dinner_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];


    protected static function boot()
    {
        parent::boot();

        // تحديث المبلغ الإجمالي عند حفظ الطلب
        static::saving(function ($model) {
            $model->updateTotalAmount();
        });

        static::updated(function ($model) {
            $model->updateScheduledInstitutionOrderStatus();
        });
    }



    public function shouldUpdateOrderStatus(): bool
    {
        return $this->wasChanged('status') &&
               $this->getOriginal('status') === 'confirmed' &&
               $this->status === 'delivered' &&
               Auth::user() &&
               Auth::user()->hasRole('kitchen');
    }


    public function updateScheduledInstitutionOrderStatus()
    {

            if (!$this->shouldUpdateOrderStatus()) {
                    return false;
            }
            return DB::transaction(function () {
                try {

                    $order = self::where('id', $this->id)
                        ->lockForUpdate()
                        ->first();

                    $kitchen = $order->kitchen ;

                    $lockedKitchen = Kitchen::where('id', $kitchen->id)
                        ->lockForUpdate()
                        ->first();

                    $budgetBefore = $lockedKitchen->Financial_debts;
                    $orderAmount = $this->total_amount;

                    $newBudget = $budgetBefore - $orderAmount;

                    $lockedKitchen->Financial_debts = $newBudget;
                    $lockedKitchen->save();

                } catch (\Exception $e) {
                    DB::rollBack() ;
                    throw $e;
                }

            }) ;
    }









    /**
     * العلاقات الأساسية
     */
    public function institution()
    {
        return $this->belongsTo(OfficialInstitution::class, 'institution_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class, 'kitchen_id');
    }

    public function orderMeals()
    {
        return $this->hasMany(ScheduledInstitutionOrderMeal::class, 'order_id');
    }

    /**
     * تحديث المبلغ الإجمالي
     */
    public function updateTotalAmount(): void
    {
        $this->total_amount = $this->orderMeals()->sum('total_price');
    }

    /**
     * إضافة وجبة للطلب
     */
    public function addMeal($dailyScheduleMealId, $quantity): void
    {
        $scheduleMeal = DailyScheduleMeal::find($dailyScheduleMealId);

        if ($scheduleMeal) {
            ScheduledInstitutionOrderMeal::updateOrCreate(
                [
                    'order_id' => $this->id,
                    'daily_schedule_meal_id' => $dailyScheduleMealId
                ],
                [
                    'quantity' => $quantity,
                    'unit_price' => $scheduleMeal->scheduled_price,
                    'total_price' => $quantity * $scheduleMeal->scheduled_price
                ]
            );

            $this->updateTotalAmount();
            $this->save();
        }
    }

    /**
     * الحصول على إجمالي عدد الوجبات
     */
    public function getTotalMealsCountAttribute(): int
    {
        return $this->orderMeals()->sum('quantity');
    }

    /**
     * الحصول على عدد الوجبات لكل نوع
     */
    public function getMealsCountByType(): array
    {
        return [
            'breakfast' => $this->orderMeals()
                ->whereHas('scheduleMeal', function($query) {
                    $query->where('meal_type', 'breakfast');
                })
                ->sum('quantity'),
            'lunch' => $this->orderMeals()
                ->whereHas('scheduleMeal', function($query) {
                    $query->where('meal_type', 'lunch');
                })
                ->sum('quantity'),
            'dinner' => $this->orderMeals()
                ->whereHas('scheduleMeal', function($query) {
                    $query->where('meal_type', 'dinner');
                })
                ->sum('quantity'),
        ];
    }

    /**
     * الحصول على المبلغ لكل نوع وجبة
     */
    public function getAmountByType(): array
    {
        return [
            'breakfast' => $this->orderMeals()
                ->whereHas('scheduleMeal', function($query) {
                    $query->where('meal_type', 'breakfast');
                })
                ->sum('total_price'),
            'lunch' => $this->orderMeals()
                ->whereHas('scheduleMeal', function($query) {
                    $query->where('meal_type', 'lunch');
                })
                ->sum('total_price'),
            'dinner' => $this->orderMeals()
                ->whereHas('scheduleMeal', function($query) {
                    $query->where('meal_type', 'dinner');
                })
                ->sum('total_price'),
        ];
    }

    /**
     * دوال إدارة حالة الطلب
     */
    public function confirm(): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function deliver(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function getStatusNameAttribute(): string
    {
        return [
            'pending' => 'قيد الانتظار',
            'confirmed' => 'تم التأكيد',
            'delivered' => 'تم التوصيل',
            'cancelled' => 'ملغي'
        ][$this->status] ?? $this->status;
    }

}
