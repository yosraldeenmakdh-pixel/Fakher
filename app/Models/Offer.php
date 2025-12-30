<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'name',
        'description',
        'discount_value',
        'is_active',
        'image' ,
        'meal_id'
    ];

    protected $appends = ['linked_to'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getLinkTypeAttribute()
    {
        if ($this->categories()->count() > 0) {
            return 'category';
        } elseif ($this->meals()->count() > 0) {
            return 'meals';
        }

        return null;
    }

    public function getLinkedToAttribute()
    {
        // تحميل العلاقات إذا لم تكن محملة
        if (!$this->relationLoaded('categories')) {
            $this->load('categories');
        }

        if (!$this->relationLoaded('meals')) {
            $this->load('meals');
        }

        if ($this->categories && $this->categories->count() > 0) {
            $category = $this->categories->first();
            return "الصنف: {$category->name}";
        } elseif ($this->meals && $this->meals->count() > 0) {
            return "عدد {$this->meals->count()} وجبة";
        }

        return 'غير مربوط';
    }


    public function meals()
    {
        return $this->belongsToMany(Meal::class, 'meal_offer');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_offer');
    }

}
