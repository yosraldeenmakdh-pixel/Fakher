<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'image'];

    public function meals()
    {
        return $this->hasMany(Meal::class);
    }

    public function offer()
    {
        return $this->belongsToMany(Offer::class, 'category_offer')
                    ->where('is_active', true)
                    ->first();
    }

    // دالة للتحقق إذا كان للصنف عرض
    public function hasOffer()
    {
        return $this->belongsToMany(Offer::class, 'category_offer')
                   ->where('is_active', true)
                   ->exists();
    }

}
