<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'price', 'image', 'is_available', 'category_id','average_rating', 'ratings_count'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'average_rating' => 'decimal:2',
        'ratings_count' => 'integer'
    ];

    // protected $appends = [
    //     'average_rating',
    //     'ratings_count',
    //     // 'stars_text',
    //     // 'rating_distribution',
    //     // 'rating_percentages'
    // ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // العلاقة مع التقييمات
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function updateRatingStats()
    {
        $visibleRatings = $this->ratings()->where('is_visible', true);

        $this->update([
            'average_rating' => $visibleRatings->avg('rating') ?: 0,
            'ratings_count' => $visibleRatings->count()
        ]);
    }

}
