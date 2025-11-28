<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function institutions()
    {
        return $this->hasMany(OfficialInstitution::class);
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

    public function localOrders()
    {
        return $this->hasMany(Order::class, 'kitchen_id');
    }

    // العلاقة مع طلبات الموقع الإلكتروني
    public function onlineOrders()
    {
        return $this->hasMany(OrderOnline::class, 'kitchen_id');
    }

    public function orders()
    {
        return $this->hasMany(InstitutionOrder::class);
    }

    // العلاقة مع طلبات المؤسسات المجدولة
    public function scheduledInstitutionOrders()
    {
        return $this->hasMany(ScheduledInstitutionOrder::class, 'kitchen_id');
    }

}
