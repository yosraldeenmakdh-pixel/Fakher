<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    protected $casts = [
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'Financial_debts' => 'decimal:2',
    ];

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
}
