<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InstitutionPayment extends Model
{
    protected $fillable = [
        'institution_id',
        'amount',
        'transaction_reference',
        'verification_file',
        'status',
        'notes',
        'rejection_reason',
        'verified_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
    ];


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

                $institution = $this->institution;

                // قفل المؤسسة لمنع التنافس
                $lockedInstitution = OfficialInstitution::where('id', $institution->id)
                    ->lockForUpdate()
                    ->first();

                $budgetBefore = $lockedInstitution->Financial_debts;
                $paymentAmount = $this->amount;

                // 🔽 عملية الخصم من الميزانية
                $newBudget = $budgetBefore + $paymentAmount;


                // تحديث ميزانية المؤسسة
                $lockedInstitution->Financial_debts = $newBudget;
                $lockedInstitution->save();

                InstitutionFinancialTransaction::create([
                    'institution_id' => $institution->id,
                    'payment_id' => $this->id,
                    'transaction_type' => 'payment',
                    'amount' => $paymentAmount,
                    'balance_before' => $budgetBefore,
                    'balance_after' => $newBudget,
                    'status' => 'completed',
                    'transaction_date' => $this->verified_at ?? now(),
                ]);

                $kitchen = $institution->kitchen ;
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
                Db::rollBack() ;
                throw $e;
            }

        }) ;
    }

    public function institution()
    {
        return $this->belongsTo(OfficialInstitution::class, 'institution_id');
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
