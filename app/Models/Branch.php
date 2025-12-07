<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'image', 'description' ,'kitchen_id'
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

}
