<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class OfficialInstitution extends Model
{
    protected $fillable = [
        'user_id' ,
        'branch_id' ,
        'kitchen_id' ,
        'name' ,
        'contract_number',
        'contract_start_date',
        'contract_end_date',
        'contract_status',
        'Financial_debts',
        // 'contact_person',
        'institution_type' ,
        'contact_phone',
        'contact_email',
        'special_instructions'
    ];

    // protected $casts = [
    //     'contract_start_date' => 'date',
    //     'contract_end_date' => 'date',
    //     'Financial_debts' => 'decimal:2',
    // ];

    /**
     * العلاقة مع طلبات المؤسسة
     */
    public function orders()
    {
        return $this->hasMany(InstitutionOrder::class, 'institution_id');
    }

    public function scheduledInstitutionOrders()
    {
        return $this->hasMany(ScheduledInstitutionOrder::class, 'institution_id');
    }

    /**
     * العلاقة مع المدفوعات
     */
    public function payments()
    {
        return $this->hasMany(InstitutionPayment::class, 'institution_id');
    }

    public function financialTransactions()
    {
        return $this->hasMany(InstitutionFinancialTransaction::class, 'institution_id');
    }

    public function getTotalPaymentsAttribute()
    {
        return $this->payments()->where('status', 'verified')->sum('amount');
    }


    public function getRemainingBalanceAttribute()
    {
        return $this->Financial_debts - $this->total_payments;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function getAccountStatement($startDate = null, $endDate = null)
    {
        $query = $this->financialTransactions()
                    ->completed()
                    ->orderBy('transaction_date', 'asc')
                    ->orderBy('id', 'asc');

        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * الحصول على الرصيد الحالي
     */
    public function getCurrentBalanceAttribute()
    {
        $lastTransaction = $this->financialTransactions()
            ->completed()
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $lastTransaction ? $lastTransaction->balance_after : 0;
    }






//     private function encryptValue($value)
//     {
//         if (empty($value) && $value !== 0 && $value !== '0') return $value;

//         // مفتاح ثابت للتشفير
//         $key = md5(config('app.key'));
//         $key = substr($key, 0, 24); // 24 حرف للمفتاح

//         // تحويل أي قيمة إلى نص
//         $value = (string)$value;

//         // طريقة تشفير XOR بسيطة
//         $result = '';
//         for ($i = 0; $i < strlen($value); $i++) {
//             $result .= $value[$i] ^ $key[$i % strlen($key)];
//         }

//         // إرجاع بصيغة قصيرة
//         return 'E' . base64_encode($result);
//     }

//     /**
//      * فك التشفير
//      */
//     private function decryptValue($value)
//     {
//         if (!is_string($value) || strlen($value) < 2 || $value[0] !== 'E') {
//             return $value;
//         }

//         $encoded = substr($value, 1);
//         $encrypted = base64_decode($encoded);

//         if ($encrypted === false) {
//             return $value;
//         }

//         $key = md5(config('app.key'));
//         $key = substr($key, 0, 24);

//         $result = '';
//         for ($i = 0; $i < strlen($encrypted); $i++) {
//             $result .= $encrypted[$i] ^ $key[$i % strlen($key)];
//         }

//         return $result;
//     }

//     // **Accessors معدلة للتعامل مع الأرقام**
//     public function getNameAttribute($value) {
//         return $this->decryptValue($value);
//     }

//     // public function getFinancialDebtsAttribute($value) {
//     //     // نتحقق أولاً إذا كانت القيمة مشفرة
//     //     if (is_string($value) && strlen($value) > 1 && $value[0] === 'E') {
//     //         $decrypted = $this->decryptValue($value);
//     //         return is_numeric($decrypted) ? (float)$decrypted : 0;
//     //     }

//     //     // إذا لم تكن مشفرة، نعيدها كرقم
//     //     return is_numeric($value) ? (float)$value : 0;
//     // }

//     public function getContactPhoneAttribute($value) {
//         return $this->decryptValue($value);
//     }

//     public function getContactEmailAttribute($value) {
//         return $this->decryptValue($value);
//     }

//     public function getSpecialInstructionsAttribute($value) {
//         return $this->decryptValue($value);
//     }

//     // **Mutators معدلة للتعامل مع الأرقام**
//     public function setNameAttribute($value) {
//         $this->attributes['name'] = $this->encryptValue($value);
//     }

//     // public function setFinancialDebtsAttribute($value) {
//     //     // تأكد من أن القيمة رقمية قبل التشفير
//     //     $value = is_numeric($value) ? (float)$value : 0;
//     //     $this->attributes['Financial_debts'] = $this->encryptValue($value);
//     // }

//     public function setContactPhoneAttribute($value) {
//         $this->attributes['contact_phone'] = $this->encryptValue($value);
//     }

//     public function setContactEmailAttribute($value) {
//         $this->attributes['contact_email'] = $this->encryptValue($value);
//     }

//     public function setSpecialInstructionsAttribute($value) {
//         $this->attributes['special_instructions'] = $value ? $this->encryptValue($value) : null;
//     }

//     public function setFinancialDebtsAttribute($value)
// {
//     // تحويل الرقم إلى نص ومضاعفته 1000 ثم تشفير
//     $numericValue = is_numeric($value) ? (float) $value : 0;

//     // طريقة تشفير بسيطة جداً
//     $encrypted = base64_encode($numericValue * 1000);

//     // تخزين مع علامة F للتمييز
//     $this->attributes['Financial_debts'] = 'F' . $encrypted;
// }

// public function getFinancialDebtsAttribute($value)
// {
//     // إذا كانت القيمة تبدأ بـ F، فهي مشفرة
//     if (is_string($value) && !empty($value) && $value[0] === 'F') {
//         $encrypted = substr($value, 1);
//         $decoded = base64_decode($encrypted);

//         if ($decoded !== false && is_numeric($decoded)) {
//             return (float) ($decoded / 1000);
//         }
//     }

//     // إذا لم تكن مشفرة
//     return is_numeric($value) ? (float) $value : 0;
// }

}
