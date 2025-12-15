<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class KitchenPayment extends Model
{
    protected $fillable = [
        'kitchen_id',
        'amount',
        'transaction_reference',
        'verification_file',
        'status',
        'notes',
        'rejection_reason',
        'verified_at'
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];


    public function getAmountAttribute($value)
    {
        if (is_null($value)) {
            return 0;
        }

        try {
            return (float) Crypt::decryptString($value);
        } catch (\Exception $e) {
            return (float) $value;
        }
    }
    // Mutator للتشفير تلقائياً
    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = Crypt::encryptString((string) $value);
    }



    protected static function boot()
    {
        parent::boot();

        static::updated(function ($model) {
                $model->deductFromInstitutionBudget();
        });


    }


    public function shouldProcessFinancialUpdate(): bool
    {
        return $this->wasChanged('status') &&
               $this->getOriginal('status') === 'pending' &&
               $this->status === 'verified';
    }


    public function deductFromInstitutionBudget()
    {
        if (!$this->shouldProcessFinancialUpdate()) {
                return false;
        }
        return DB::transaction(function () {

            try {

                $kitchen = $this->kitchen;

                // $kitchen = $institution->kitchen ;
                $lockedKitchen = Kitchen::where('id', $kitchen->id)
                    ->lockForUpdate()
                    ->first();

                $budgetBeforeForKitchen = $lockedKitchen->Financial_debts;
                $orderAmountForKitchen = $this->amount;

                $newBudgetForKitchen = $budgetBeforeForKitchen + $orderAmountForKitchen;

                $lockedKitchen->Financial_debts = $newBudgetForKitchen;
                $lockedKitchen->save();

                KitchenFinancialTransaction::create([
                    'kitchen_id' => $kitchen->id,
                    'payment_id' => $this->id,
                    'transaction_type' => 'payment',
                    'amount' => $orderAmountForKitchen ,
                    'balance_before' => $budgetBeforeForKitchen,
                    'balance_after' => $newBudgetForKitchen,
                    'status' => 'completed',
                    'transaction_date' => now(),
                ]);


            } catch (\Exception $e) {
                DB::rollBack() ;
                throw $e;
            }

        }) ;
    }

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class, 'kitchen_id');
    }

    /**
     * تحديد إذا كان الدفع مفعل
     */
    public function getIsVerifiedAttribute(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * تحديد إذا كان الدفع مرفوض
     */
    public function getIsRejectedAttribute(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * تحديد إذا كان الدفع معلق
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

}
