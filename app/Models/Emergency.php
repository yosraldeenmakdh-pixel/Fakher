<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Emergency extends Model
{
    protected $fillable = [
        'institution_id',
        'branch_id',
        'kitchen_id',
        'order_date',
        'persons',
        'total_amount',
        'status',
        'special_instructions',
        'confirmed_at',
        'delivered_at',
    ];

    protected $casts = [
        'order_date' => 'datetime' ,
        'total_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * العلاقة مع المؤسسة
     */
    public function institution()
    {
        return $this->belongsTo(OfficialInstitution::class, 'institution_id');
    }

    /**
     * العلاقة مع الفرع
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * العلاقة مع المطبخ
     */
    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class, 'kitchen_id');
    }

    /**
     * العلاقة مع عناصر الطلب
     */
    public function items()
    {
        return $this->hasMany(EmergencyItem::class);
    }


    protected static function boot()
    {
        parent::boot();

        static::updated(function ($model) {

                $model->updateInstitutionOrderStatus();

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

    /**
     * تحديث حالة الطلب في جدول institution_orders
     */
    public function updateInstitutionOrderStatus()
    {

            if (!$this->shouldUpdateOrderStatus()) {
                    return false;
            }
            return DB::transaction(function () {
                try {

                    $freshEmergncy = self::where('id', $this->id)
                        ->lockForUpdate()
                        ->first();

                    $institution = $freshEmergncy->institution ;
                    $lockedInstitution = OfficialInstitution::where('id', $institution->id)
                        ->lockForUpdate()
                        ->first();

                    $budgetBefore = $lockedInstitution->Financial_debts;
                    $orderAmount = $this->total_amount;

                    $newBudget = $budgetBefore - $orderAmount;

                    $lockedInstitution->Financial_debts = $newBudget;
                    $lockedInstitution->save();


                    $kitchen = $freshEmergncy->kitchen ;
                    $lockedKitchen = Kitchen::where('id', $kitchen->id)
                        ->lockForUpdate()
                        ->first();

                    $budgetBeforeForKitchen = $lockedKitchen->Financial_debts;
                    $orderAmountForKitchen = $this->total_amount;

                    $newBudgetForKitchen = $budgetBeforeForKitchen - $orderAmountForKitchen;

                    $lockedKitchen->Financial_debts = $newBudgetForKitchen;
                    $lockedKitchen->save();



                } catch (\Exception $e) {
                    throw $e;
                }

            }) ;
    }



}
