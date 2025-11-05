<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicRating extends Model
{
    protected $fillable = [
        'user_id',
        'rating',
        'comment',
        'is_visible'
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s', // تنسيق مخصص
        'updated_at' => 'datetime:Y-m-d H:i:s', // تنسيق مخصص
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public static function updateOrCreateRating($userId, $data)
    {
        return static::updateOrCreate(
            ['user_id' => $userId],
            [
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]
        );
    }

    public static function getUserRating($userId)
    {
        return static::where('user_id', $userId)->first();
    }

    public static function getRatingStats()
    {
        $total = self::count();

        return [
            'average_rating' => $total > 0 ? round(self::avg('rating'), 2) : 0,
            'total_ratings' => $total,
            'rating_distribution' => self::groupBy('rating')
                ->selectRaw('rating, COUNT(*) as count')
                ->get()
                ->pluck('count', 'rating')
                ->toArray(),
            'total_comments' => self::whereNotNull('comment')->count()
        ];
    }
}
