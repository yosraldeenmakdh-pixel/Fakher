<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InstitutionalMealPrice extends Model
{
    protected $fillable = [
        'meal_id',
        'scheduled_price',
        'is_active',
    ];

    protected $casts = [
        'scheduled_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

    public static function getActivePrice(int $mealId): ?float
    {
        $price = static::where('meal_id', $mealId)
                    ->where('is_active', true)
                    ->first();

        return $price ? (float) $price->scheduled_price : null;
    }


    public static function getActivePrices(): array
    {
        return static::with('meal')
                    ->where('is_active', true)
                    ->get()
                    ->keyBy('meal_id')
                    ->toArray();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }



    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->is_active) {
                static::where('meal_id', $model->meal_id)
                    ->where('is_active', true)
                    ->where('id', '!=', $model->id)
                    ->update(['is_active' => false]);
            }
        });

        static::updating(function ($model) {
            if ($model->is_active) {
                static::where('meal_id', $model->meal_id)
                    ->where('is_active', true)
                    ->where('id', '!=', $model->id)
                    ->update(['is_active' => false]);
            }
        });
    }


}
