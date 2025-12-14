<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'user_id',
        'meal_id',
        'rating',
        'comment',
        'is_visible'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_visible' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];


    protected static function booted()
    {
        static::created(function ($rating) {
            // عند إنشاء تقييم جديد، تحديث إحصائيات الوجبة
            if ($rating->meal) {
                $rating->meal->updateRatingStats();
            }
        });

        static::updated(function ($rating) {
            // عند تحديث تقييم، تحديث إحصائيات الوجبة إذا تغير التقييم أو visibility
            if ($rating->isDirty(['rating', 'is_visible'])) {
                if ($rating->meal) {
                    $rating->meal->updateRatingStats();
                }
            }
        });

        static::deleted(function ($rating) {
            // عند حذف تقييم، تحديث إحصائيات الوجبة
            if ($rating->meal) {
                $rating->meal->updateRatingStats();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }
}
