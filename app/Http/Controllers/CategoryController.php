<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Meal;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // public function index(Request $request)
    // {
    //     try {
    //         $categories = Category::all() ;

    //         return CategoryResource::collection($categories);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'فشل في جلب التصنيفات',
    //         ], 500);
    //     }
    // }


    public function index(Request $request)
    {
        try {
            $categories = Category::withCount('meals')->get();

            return response()->json([
                'data' => CategoryResource::collection($categories),
                'total_categories' => $categories->count(),
                'total_meals' => $categories->sum('meals_count')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب التصنيفات',
            ], 500);
        }
    }

    // public function mealsCountPerCategory()
    // {
    //     try {
    //         $categories = Category::withCount('meals')
    //             ->get()
    //             ->map(function ($category) {
    //                 return [
    //                     // 'branch_id' => $branch->id
    //                     'category_name' => $category->name,
    //                     'meals_count' => $category->meals_count,
    //                     // 'available_meals_count' => $branch->meals()->where('is_available', true)->count(),
    //                     // 'unavailable_meals_count' => $branch->meals()->where('is_available', false)->count()
    //                 ];
    //             });

    //         return response()->json([

    //             'data' => $categories,
    //             'total_branches' => $categories->count(),
    //             'total_meals' => $categories->sum('meals_count')
    //         ]);

    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'حدث خطأ أثناء جلب عدد الوجبات لكل صنف'
    //         ], 500);
    //     }
    // }

}
