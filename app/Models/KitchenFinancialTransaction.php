<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'datetime'
    ];

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
        return $this->belongsTo(InstitutionPayment::class, 'payment_id');
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
            'scheduled_order' => 'طلب مجدول',
            'special_order' => 'طلب خاص',
            'emergency_order' => 'طلب استنفار',
            'online_order' => 'طلب من الموقع الإلكتروني',
            'order' => 'طلب من داخل المطبخ',
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
