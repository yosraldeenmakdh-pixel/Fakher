<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kitchen extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'name',
        'contact_phone',
        'contact_email',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(InstitutionOrder::class);
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
}
