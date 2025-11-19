<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $fillable = [
        'kitchen_id',
        'name',
        'total',
        'special_instructions',
        'status',
        'delivered_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
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


    public function updateInstitutionOrderStatus()
    {

            if (!$this->shouldUpdateOrderStatus()) {
                    return false;
            }
            return DB::transaction(function () {
                try {

                    $order = self::where('id', $this->id)
                        ->lockForUpdate()
                        ->first();

                    $this->delivered_at = now() ;
                    $this->saveQuietly();

                    $kitchen = $order->kitchen ;

                    $lockedKitchen = Kitchen::where('id', $kitchen->id)
                        ->lockForUpdate()
                        ->first();

                    $budgetBefore = $lockedKitchen->Financial_debts;
                    $orderAmount = $this->total;

                    $newBudget = $budgetBefore - $orderAmount;

                    $lockedKitchen->Financial_debts = $newBudget;
                    $lockedKitchen->save();

                } catch (\Exception $e) {
                    DB::rollBack() ;
                    throw $e;
                }

            }) ;
    }


}
