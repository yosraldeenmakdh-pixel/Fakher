<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MealController extends Controller
{

    public function getAllMealsCount(Request $request){

        try {
            $mealsCount = Meal::count();

            return response()->json([
                'success' => true,
                'message' => 'تم جلب عدد الوجبات بنجاح',
                'data' => [
                    'count' => $mealsCount
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب عدد الوجبات',
            ], 500);
        }

    }

     public function getMealsByRating(Request $request)
    {

        $perPage = $request->get('per_page', 6);
        $page = $request->get('page', 1);
        $categoryId = $request->get('category_id');


        try {
            // بناء query الأساسي
            $query = Meal::with(['category' => function($query) {
                $query->select('id', 'name');
            }])
            ->where('is_available', true);

            // تطبيق الفلترة حسب التصنيف إذا موجود
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            // الترتيب حسب التقييم (الأعلى أولاً) ثم حسب عدد التقييمات لكسر التعادل
            $meals = $query->orderBy('average_rating', 'desc')
                          ->orderBy('ratings_count', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate($perPage, ['*'], 'page', $page);

            // التحقق من وجود بيانات
            if ($meals->isEmpty()) {
                return response()->json([
                    'message' => 'لا توجد وجبات متاحة',
                    'data' => [],
                    'meta' => [
                        'current_page' => $meals->currentPage(),
                        'total_pages' => $meals->lastPage(),
                        'total_meals' => $meals->total(),
                        'per_page' => $meals->perPage()
                    ],
                ], 200);
            }

            // تنسيق البيانات للإرجاع
            $formattedMeals = $meals->map(function ($meal) {
                return [
                    'id' => $meal->id,
                    'name' => $meal->name,
                    'description' => $meal->description,
                    'price' => (float) $meal->price,
                    'image' => $meal->image ? asset('uploads/' . $meal->image) : null,
                    'is_available' => (bool) $meal->is_available,
                    'average_rating' => (float) $meal->average_rating,
                    'ratings_count' => (int) $meal->ratings_count,
                    // 'stars_text' => $this->generateStarsText($meal->average_rating),
                    'category' => $meal->category ? [
                        'id' => $meal->category->id,
                        'name' => $meal->category->name
                    ] : null,
                    'created_at' => $meal->created_at->toISOString(),
                    'updated_at' => $meal->updated_at->toISOString()
                ];
            });

            DB::commit();

            return response()->json([
                'message' => 'تم جلب الوجبات بنجاح',
                'data' => $formattedMeals,
                'meta' => [
                    'current_page' => $meals->currentPage(),
                    'last_page' => $meals->lastPage(),
                    'per_page' => $meals->perPage(),
                    'total' => $meals->total(),
                ],
                'links' => [
                        'first' => $meals->url(1),
                        'last' => $meals->url($meals->lastPage()),
                        'prev' => $meals->previousPageUrl(),
                        'next' => $meals->nextPageUrl(),
                ],
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'حدث خطأ أثناء جلب الوجبات',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }



public function getMealById($id)
{
    try {
        // البحث عن الوجبة مع العلاقات
        $meal = Meal::with(['category' => function($query) {
            $query->select('id', 'name');
        }])
        ->where('is_available', true)
        ->find($id);

        // التحقق من وجود الوجبة
        if (!$meal) {
            return response()->json([
                'success' => false,
                'message' => 'الوجبة غير موجودة أو غير متاحة'
            ], 404);
        }

        // تنسيق البيانات للإرجاع
        $formattedMeal = [
            'id' => $meal->id,
            'name' => $meal->name,
            'description' => $meal->description,
            'price' => (float) $meal->price,
            'image' => $meal->image ? asset('uploads/' . $meal->image) : null, // تأكد من المسار الصحيح
            'is_available' => (bool) $meal->is_available,
            'average_rating' => (float) $meal->average_rating,
            'ratings_count' => (int) $meal->ratings_count,
            'category' => $meal->category ? [
                'id' => $meal->category->id,
                'name' => $meal->category->name
            ] : null,
            'created_at' => $meal->created_at->toISOString(),
            'updated_at' => $meal->updated_at->toISOString()
        ];

        return response()->json([
            'success' => true,
            'message' => 'تم جلب الوجبة بنجاح',
            'data' => $formattedMeal
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء جلب الوجبة'
        ], 500);
    }
}

}
