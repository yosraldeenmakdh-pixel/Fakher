<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OnlineOrderConfirmation extends Model
{
    protected $fillable = [
        'order_id',
        'kitchen_id',
        'notes',
        'order_number',
        'delivery_date',
        'delivery_time',
        'total_amount',
        'order_items',
        'status',
        'special_instructions',
        'delivered_at',

    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function order()
    {
        return $this->belongsTo(OrderOnline::class, 'order_id');
    }

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
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

                    $freshConfirmation = self::where('id', $this->id)
                        ->lockForUpdate()
                        ->first();

                    $this->delivered_at = now() ;
                    $this->saveQuietly();

                    $onlineOrder = OrderOnline::where('id', $freshConfirmation->order_id)
                        ->lockForUpdate()
                        ->first();

                    if ($onlineOrder) {
                        $onlineOrder->status = 'delivered' ;
                        $onlineOrder->delivered_at = now() ;
                        $onlineOrder->saveQuietly();
                    }
                    $kitchen = $onlineOrder->kitchen ;

                    $lockedKitchen = Kitchen::where('id', $kitchen->id)
                        ->lockForUpdate()
                        ->first();

                    $budgetBefore = $lockedKitchen->Financial_debts;
                    $orderAmount = $this->total_amount;

                    $newBudget = $budgetBefore - $orderAmount;

                    $lockedKitchen->Financial_debts = $newBudget;
                    $lockedKitchen->save();

                    KitchenFinancialTransaction::create([
                        'kitchen_id' => $kitchen->id,
                        'transaction_type' => 'online_order',
                        'order_id' => $freshConfirmation->order_id,
                        'order_type' => get_class($this),
                        'amount' => $orderAmount ,
                        'balance_before' => $budgetBefore,
                        'balance_after' => $newBudget,
                        'status' => 'completed',
                        'transaction_date' => now(),
                    ]);



                } catch (\Exception $e) {
                    DB::rollBack() ;
                    throw $e;
                }

            }) ;
    }



}
