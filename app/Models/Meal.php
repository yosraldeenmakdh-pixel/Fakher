<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description','preparation_minutes', 'price', 'image', 'is_available','meal_type', 'category_id','average_rating', 'ratings_count'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'average_rating' => 'decimal:1',
        'ratings_count' => 'integer'
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }



    public function onlineOrderItems()
    {
        return $this->hasMany(OrderOnlineItem::class);
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



    /**
     * الحصول على السعر المجدول
     */
    public function getScheduledPriceAttribute()
    {
        return $this->scheduledPrice ? $this->scheduledPrice->scheduled_price : $this->price;
    }

    public function hasScheduledPrice()
    {
        return (bool) $this->scheduledPrice;
    }

    /**
     * الحصول على معلومات السعر
     */
    public function getPriceInfo()
    {
        $scheduledPrice = $this->scheduledPrice;
        $hasScheduledPrice = (bool) $scheduledPrice;
        $scheduledPriceValue = $hasScheduledPrice ? $scheduledPrice->scheduled_price : null;

        return [
            'original_price' => $this->price,
            'scheduled_price' => $scheduledPriceValue,
            'final_price' => $scheduledPriceValue ?: $this->price,
            'has_discount' => $hasScheduledPrice && $scheduledPriceValue < $this->price,
            'discount_percentage' => $hasScheduledPrice && $this->price > 0
                ? round((($this->price - $scheduledPriceValue) / $this->price) * 100, 2)
                : 0,
            'is_scheduled_price' => $hasScheduledPrice
        ];
    }




public function getTotalSalesAttribute()
{
    $orderItems = $this->orderItems()->sum('quantity');
    $onlineItems = $this->onlineOrderItems()->sum('quantity');
    $institutionItems = $this->institutionOrderItems()->sum('quantity');

    return $orderItems + $onlineItems + $institutionItems ;
}

}
