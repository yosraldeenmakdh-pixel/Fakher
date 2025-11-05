<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $fillable = [
        'kitchen_id',
        'name',
        'capacity',
        'status',
        // 'location',
        'description'
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    // public function reservations()
    // {
    //     return $this->hasMany(Reservation::class);
    // }
}
