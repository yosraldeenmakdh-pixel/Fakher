<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description','preparation_minutes', 'price','is_available','meal_type', 'category_id','average_rating', 'ratings_count'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'average_rating' => 'decimal:1',
        'ratings_count' => 'integer'
    ];




    // public function media()
    // {
    //     return $this->hasMany(MealMedia::class)->orderBy('order');
    // }

    // public function images()
    // {
    //     return $this->hasMany(MealMedia::class)->where('type', 'image')->orderBy('order');
    // }

    // public function videos()
    // {
    //     return $this->hasMany(MealMedia::class)->where('type', 'video')->orderBy('order');
    // }

    // public function primaryImage()
    // {
    //     return $this->hasOne(MealMedia::class)->where('type', 'image')->where('is_primary', true);
    // }




    public function offer()
    {
        return $this->belongsToMany(Offer::class, 'meal_offer')
                    ->where('is_active', true)
                    ->first(); // نأخذ العرض الأول فقط
    }




    // دالة للتحقق إذا كان للوجبة عرض
    public function hasOffer()
    {
        // أولاً: نتحقق من وجود عرض مباشر على الوجبة
        $mealOffer = $this->belongsToMany(Offer::class, 'meal_offer')
                      ->where('is_active', true)
                      ->exists();

        if ($mealOffer) {
            return true;
        }

        // ثانياً: نتحقق من وجود عرض على الصنف
        if ($this->category) {
            $categoryOffer = $this->category->hasOffer();
            return $categoryOffer;
        }

        return false;
    }

    // دالة للحصول على السعر بعد الخصم
    public function getDiscountedPrice()
    {
        // أولاً: نبحث عن عرض مباشر على الوجبة
        $mealOffer = $this->belongsToMany(Offer::class, 'meal_offer')
                    ->where('is_active', true)
                    ->first();

        if ($mealOffer) {
            return $this->applyDiscount($mealOffer);
        }

        // ثانياً: نبحث عن عرض على الصنف
        if ($this->category && $this->category->hasOffer()) {
            $categoryOffer = $this->category->offer();
            return $this->applyDiscount($categoryOffer);
        }

        return $this->price;
    }

    // دالة تطبيق الخصم
    private function applyDiscount($offer)
    {
        $discount = $offer->discount_value;
        $originalPrice = $this->price;

        if (empty($discount)) {
            return $originalPrice;
        }

        if (str_contains($discount, '%')) {
            $percentage = (float) str_replace('%', '', $discount);
            return $originalPrice * (1 - ($percentage / 100));
        }

        if (str_contains($discount, '$')) {
            $fixedAmount = (float) str_replace('$', '', $discount);
            return max(0, $originalPrice - $fixedAmount);
        }

        $percentage = (float) $discount;
        return $originalPrice * (1 - ($percentage / 100));
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function branches()
    {
        return $this->belongsToMany(Branch::class) ;
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
