<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// تم الرفع

class RatingController extends Controller
{


    public function storeOrUpdate(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string' ,
            'meal_id' => 'required|integer|exists:meals,id',
        ], [
            'meal_id.required' => 'معرف الوجبة مطلوب', // تصحيح: meal_id بدل id
            'meal_id.integer' => 'معرف الوجبة يجب أن يكون رقماً',
            'meal_id.exists' => 'الوجبة غير موجودة',
            'rating.required' => 'التقييم مطلوب',
            'rating.integer' => 'التقييم يجب أن يكون رقماً',
            'rating.min' => 'التقييم يجب أن يكون على الأقل 1',
            'rating.max' => 'التقييم يجب أن يكون على الأكثر 5'
        ]);

        $meal = Meal::where('id',$request->meal_id)->first();

        if (!$meal) {
            return response()->json([
                'message' => 'الوجبة غير موجودة'
            ], 404);
        }

        // البحث عن التقييم الحالي للمستخدم لهذه الوجبة
        $existingRating = Rating::where('user_id', Auth::id())
            ->where('meal_id', $request->meal_id)
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
                    'meal_id' => $request->meal_id,
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
                ]
            ], $isUpdate ? 200 : 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'حدث خطأ أثناء ' . ($isUpdate ? 'تحديث' : 'إضافة') . ' التقييم'
            ], 500);
        }
    }


    public function getMealRatings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:meals,id',
        ], [
            'id.required' => 'معرف الوجبة مطلوب',
            'id.integer' => 'معرف الوجبة يجب أن يكون رقماً',
            'id.exists' => 'الوجبة غير موجودة'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        $meal = Meal::where('id',$request->id)->first() ;


        $ratings = Rating::with(['user' => function($query) {
                $query->select('id', 'name', 'email', 'image');
            }])
            ->where('meal_id', $meal->id)
            ->where('is_visible', true)
            ->orderBy('created_at', 'DESC')
            ->orderBy('rating', 'DESC') ;

        $ratings->transform(function ($rating) {
                if ($rating->user && $rating->user->image) {
                    // إضافة URL كامل للصورة
                    $rating->user->image_url = asset('uploads/' . $rating->user->image);
                    // 'image' => $user->image ? asset('uploads/' . $user->image) : null,
                }
                return $rating;
            });

        return response()->json([
            'meal_id' => $meal->id,
            'meal_name' => $meal->name,
            'total_ratings' => $ratings->total(),
            'average_rating' => $meal->average_rating,
            'ratings' => $ratings
        ]);
    }

}
