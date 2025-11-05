<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstitutionOrderConfirmation extends Model
{
    protected $fillable = [
        'order_id',
        'kitchen_id',
        'notes',
        'order_number',
        'delivery_date',
        'delivery_time',
        'total_amount',
        'status',
        'special_instructions',
        'delivered_at',

    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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

                    $freshConfirmation = self::where('id', $this->id)
                        ->lockForUpdate()
                        ->first();

                    $this->delivered_at = now() ;
                    $this->saveQuietly();

                    $institutionOrder = \App\Models\InstitutionOrder::where('id', $freshConfirmation->order_id)
                        ->lockForUpdate()
                        ->first();

                    if ($institutionOrder) {
                        $institutionOrder->status = 'delivered' ;
                        $institutionOrder->delivered_at = now() ;
                        $institutionOrder->saveQuietly();
                    }
                    $institution = $institutionOrder->institution ;

                    $lockedInstitution = OfficialInstitution::where('id', $institution->id)
                        ->lockForUpdate()
                        ->first();

                    $budgetBefore = $lockedInstitution->Financial_debts;
                    $orderAmount = $this->total_amount;

                    $newBudget = $budgetBefore - $orderAmount;

                    $lockedInstitution->Financial_debts = $newBudget;
                    $lockedInstitution->save();



                } catch (\Exception $e) {
                    throw $e;
                }

            }) ;
    }

    public function order()
    {
        return $this->belongsTo(InstitutionOrder::class, 'order_id');
    }

    /**
     * العلاقة مع المستخدم (المطبخ)
     */
    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class, 'kitchen_id');
    }

    /**
     * الحصول على معلومات التأكيد
     */
    // public function getConfirmationInfoAttribute()
    // {
    //     return [
    //         'order_number' => $this->order->order_number,
    //         'kitchen_name' => $this->kitchen->name,
    //         'confirmed_at' => $this->created_at->format('Y-m-d H:i:s'),
    //         'notes' => $this->notes,
    //     ];
    // }

}
