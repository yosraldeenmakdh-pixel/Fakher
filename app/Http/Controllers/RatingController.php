<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{

    public function store(Request $request, $mealId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);


        $existingRating = Rating::where('user_id', Auth::id())
            ->where('meal_id', $mealId)
            ->first();

        if ($existingRating) {
            return response()->json([
                'message' => 'لقد قمت بتقييم هذه الوجبة مسبقاً'
            ], 400);
        }

        $meal = Meal::find($mealId);
        if (!$meal) {
            return response()->json([
                'message' => 'الوجبة غير موجودة'
            ], 404);
        }

        // استخدام transaction للتأكد من سلامة البيانات
        DB::beginTransaction();

        try {
            // إنشاء التقييم
            $rating = Rating::create([
                'user_id' => Auth::id(),
                'meal_id' => $mealId,
                'rating' => $request->rating,
                'comment' => $request->comment
            ]);

            // تحديث إحصائيات الوجبة
            $meal->updateRatingStats();

            DB::commit();

            // إعادة تحميل البيانات المحدثة
            $meal->refresh();

            return response()->json([
                'message' => 'تم إضافة التقييم بنجاح',
                'rating' => $rating,
                'meal_stats' => [
                    'average_rating' => $meal->average_rating,
                    'ratings_count' => $meal->ratings_count,
                    // 'stars_text' => $meal->stars_text
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء إضافة التقييم'
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $rating = Rating::where('user_id', Auth::id())->first();

        if (!$rating) {
            return response()->json([
                'message' => 'التقييم غير موجود'
            ], 404);
        }

        $oldRating = $rating->rating;
        $mealId = $rating->meal_id;

        DB::beginTransaction();

        try {
            $rating->update([
                'rating' => $request->rating,
                'comment' => $request->comment
            ]);

            // تحديث إحصائيات الوجبة فقط إذا تغير التقييم
            if ($oldRating != $request->rating) {
                $meal = Meal::find($mealId);
                $meal->updateRatingStats();
                $meal->refresh();
            }

            DB::commit();

            return response()->json([
                'message' => 'تم تحديث التقييم بنجاح',
                'rating' => $rating,
                'meal_stats' => [
                    'average_rating' => $meal->average_rating,
                    'ratings_count' => $meal->ratings_count,
                    // 'stars_text' => $meal->stars_text
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء تحديث التقييم'
            ], 500);
        }
    }


    // public function getMealStats($mealId)
    // {
    //     $meal = Meal::findOrFail($mealId);

    //     if (!$meal) {
    //         return response()->json([
    //             'message' => 'الوجبة غير موجودة'
    //         ], 404);
    //     }

    //     return response()->json([
    //         'meal_id' => $mealId,
    //         'average_rating' => $meal->average_rating,
    //         'ratings_count' => $meal->ratings_count,
    //         'stars_text' => $meal->stars_text,
    //         'rating_distribution' => $meal->getRatingDistribution()
    //     ]);
    // }

}
