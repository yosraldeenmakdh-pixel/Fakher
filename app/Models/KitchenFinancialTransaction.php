<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class KitchenFinancialTransaction extends Model
{
    protected $fillable = [
        'kitchen_id',
        'payment_id',
        'transaction_type',
        'order_id',
        'order_type',
        'description',
        'amount',
        'balance_before',
        'balance_after',
        'status',
        'transaction_date'
    ];

    protected $casts = [
        'transaction_date' => 'datetime'
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


    public function getBalanceBeforeAttribute($value)
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
    public function setBalanceBeforeAttribute($value)
    {
        $this->attributes['balance_before'] = Crypt::encryptString((string) $value);
    }


    public function getBalanceAfterAttribute($value)
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
    public function setBalanceAfterAttribute($value)
    {
        $this->attributes['balance_after'] = Crypt::encryptString((string) $value);
    }





    /**
     * العلاقة مع المطبخ
     */
    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    /**
     * العلاقة مع الدفعة
     */
    public function payment()
    {
        return $this->belongsTo(KitchenPayment::class, 'payment_id');
    }

    /**
     * العلاقة متعددة الأشكال مع الطلبات
     */
    public function order()
    {
        return $this->morphTo();
    }

    /**
     * الحصول على نوع الحركة بالعربية
     */
    public function getTransactionTypeArabicAttribute()
    {
        $types = [
            'online_order' => 'طلب من الموقع الإلكتروني',
            'payment' => 'دفعة'
        ];

        return $types[$this->transaction_type] ?? $this->transaction_type;
    }

    /**
     * الحصول على اتجاه المبلغ (دائن/مدين)
     */
    public function getAmountDirectionAttribute()
    {
        return $this->amount >= 0 ? 'دائن' : 'مدين';
    }

    /**
     * الحصول على القيمة المطلقة للمبلغ
     */
    public function getAbsoluteAmountAttribute()
    {
        return abs($this->amount);
    }

    /**
     * البحث بالمدى الزمني
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * البحث بنوع الحركة
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * الحركات المكتملة فقط
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
