<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Kitchen extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'name',
        'description',
        'address',
        'opening_time',
        'closing_time',
        'contact_phone',
        'contact_email',
        'is_active' ,
        'Financial_debts',
    ];


    public function getFinancialDebtsAttribute($value)
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
    public function setFinancialDebtsAttribute($value)
    {
        $this->attributes['Financial_debts'] = Crypt::encryptString((string) $value);
    }




    public function getNameAttribute($value)
    {
        if (is_null($value)) {
            return '';
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = Crypt::encryptString($value);
    }

    // =================== contact_phone ===================
    public function getContactPhoneAttribute($value)
    {
        if (is_null($value)) {
            return '';
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function setContactPhoneAttribute($value)
    {
        $this->attributes['contact_phone'] = Crypt::encryptString($value);
    }




    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }




    public function pendingOrders()
    {
        return $this->orders()->where('status', 'pending');
    }

    public function confirmedOrders()
    {
        return $this->orders()->where('status', 'confirmed');
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }


    // العلاقة مع طلبات الموقع الإلكتروني
    public function onlineOrders()
    {
        return $this->hasMany(OrderOnline::class, 'kitchen_id');
    }

}
