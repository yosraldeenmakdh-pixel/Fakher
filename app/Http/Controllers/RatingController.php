<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{


    public function storeOrUpdate(Request $request, $mealId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $meal = Meal::find($mealId);
        if (!$meal) {
            return response()->json([
                'message' => 'الوجبة غير موجودة'
            ], 404);
        }

        // البحث عن التقييم الحالي للمستخدم لهذه الوجبة
        $existingRating = Rating::where('user_id', Auth::id())
            ->where('meal_id', $mealId)
            ->first();

        DB::beginTransaction();

        try {
            $isUpdate = false;
            $oldRating = null;

            if ($existingRating) {
                // تحديث التقييم الحالي
                $isUpdate = true;
                $oldRating = $existingRating->rating;

                $existingRating->update([
                    'rating' => $request->rating,
                    'comment' => $request->comment
                ]);

                $rating = $existingRating;

            } else {
                // إنشاء تقييم جديد
                $rating = Rating::create([
                    'user_id' => Auth::id(),
                    'meal_id' => $mealId,
                    'rating' => $request->rating,
                    'comment' => $request->comment
                ]);
            }

            // تحديث إحصائيات الوجبة
            // إذا كان تحديثاً وتغير التقييم، أو إذا كان جديداً
            if (!$isUpdate || $oldRating != $request->rating) {
                $meal->updateRatingStats();
                $meal->refresh();
            }

            DB::commit();

            return response()->json([
                'message' => $isUpdate ? 'تم تحديث التقييم بنجاح' : 'تم إضافة التقييم بنجاح',
                'rating' => $rating,
                'action' => $isUpdate ? 'updated' : 'created',
                'meal_stats' => [
                    'average_rating' => $meal->average_rating,
                    'ratings_count' => $meal->ratings_count,
                    // 'stars_text' => $meal->stars_text
                ]
            ], $isUpdate ? 200 : 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'حدث خطأ أثناء ' . ($isUpdate ? 'تحديث' : 'إضافة') . ' التقييم'
            ], 500);
        }
    }






    public function getMealRatings($mealId, Request $request)
    {
        $meal = Meal::find($mealId);
        if (!$meal) {
            return response()->json([
                'message' => 'الوجبة غير موجودة'
            ], 404);
        }

        $page = $request->get('page', 1); // الحصول على رقم الصفحة من Request

        $ratings = Rating::with(['user' => function($query) {
                $query->select('id', 'name', 'email', 'image');
            }])
            ->where('meal_id', $mealId)
            ->where('is_visible', true)
            ->orderBy('rating', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->paginate(5, ['*'], 'page', $page);

        $ratings->getCollection()->transform(function ($rating) {
                if ($rating->user && $rating->user->image) {
                    // إضافة URL كامل للصورة
                    $rating->user->image_url = asset('uploads/' . $rating->user->image);
                    // 'image' => $user->image ? asset('uploads/' . $user->image) : null,
                }
                return $rating;
            });

        return response()->json([
            'meal_id' => $mealId,
            'meal_name' => $meal->name,
            'total_ratings' => $ratings->total(),
            'average_rating' => $meal->average_rating,
            'current_page' => $ratings->currentPage(),
            'last_page' => $ratings->lastPage(),
            'per_page' => $ratings->perPage(),
            'has_more_pages' => $ratings->hasMorePages(),
            'next_page_url' => $ratings->nextPageUrl(),
            'prev_page_url' => $ratings->previousPageUrl(),
            'ratings' => $ratings->items()
        ]);
    }









    // public function store(Request $request, $mealId)
    // {
    //     $request->validate([
    //         'rating' => 'required|integer|min:1|max:5',
    //         'comment' => 'nullable|string'
    //     ]);


    //     $existingRating = Rating::where('user_id', Auth::id())
    //         ->where('meal_id', $mealId)
    //         ->first();

    //     if ($existingRating) {
    //         return response()->json([
    //             'message' => 'لقد قمت بتقييم هذه الوجبة مسبقاً'
    //         ], 400);
    //     }

    //     $meal = Meal::find($mealId);
    //     if (!$meal) {
    //         return response()->json([
    //             'message' => 'الوجبة غير موجودة'
    //         ], 404);
    //     }

    //     // استخدام transaction للتأكد من سلامة البيانات
    //     DB::beginTransaction();

    //     try {
    //         // إنشاء التقييم
    //         $rating = Rating::create([
    //             'user_id' => Auth::id(),
    //             'meal_id' => $mealId,
    //             'rating' => $request->rating,
    //             'comment' => $request->comment
    //         ]);

    //         // تحديث إحصائيات الوجبة
    //         $meal->updateRatingStats();

    //         DB::commit();

    //         // إعادة تحميل البيانات المحدثة
    //         $meal->refresh();

    //         return response()->json([
    //             'message' => 'تم إضافة التقييم بنجاح',
    //             'rating' => $rating,
    //             'meal_stats' => [
    //                 'average_rating' => $meal->average_rating,
    //                 'ratings_count' => $meal->ratings_count,
    //                 // 'stars_text' => $meal->stars_text
    //             ]
    //         ], 201);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'حدث خطأ أثناء إضافة التقييم'
    //         ], 500);
    //     }
    // }

    // public function update(Request $request)
    // {
    //     $request->validate([
    //         'rating' => 'required|integer|min:1|max:5',
    //         'comment' => 'nullable|string'
    //     ]);

    //     $rating = Rating::where('user_id', Auth::id())->first();

    //     if (!$rating) {
    //         return response()->json([
    //             'message' => 'التقييم غير موجود'
    //         ], 404);
    //     }

    //     $oldRating = $rating->rating;
    //     $mealId = $rating->meal_id;

    //     DB::beginTransaction();

    //     try {
    //         $rating->update([
    //             'rating' => $request->rating,
    //             'comment' => $request->comment
    //         ]);

    //         // تحديث إحصائيات الوجبة فقط إذا تغير التقييم
    //         if ($oldRating != $request->rating) {
    //             $meal = Meal::find($mealId);
    //             $meal->updateRatingStats();
    //             $meal->refresh();
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'تم تحديث التقييم بنجاح',
    //             'rating' => $rating,
    //             'meal_stats' => [
    //                 'average_rating' => $meal->average_rating,
    //                 'ratings_count' => $meal->ratings_count,
    //                 // 'stars_text' => $meal->stars_text
    //             ]
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'حدث خطأ أثناء تحديث التقييم'
    //         ], 500);
    //     }
    // }


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
