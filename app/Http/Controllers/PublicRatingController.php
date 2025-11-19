<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRatingRequest;
use App\Models\PublicRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// تم الرفع

class PublicRatingController extends Controller
{
    public function store(StoreRatingRequest $request)
    {

        DB::beginTransaction();

        try {

            $userId = Auth::id() ;

            $validatedData = $request->validated();

            $previousRating = PublicRating::getUserRating($userId);


            $rating = PublicRating::updateOrCreateRating(
                $userId,
                [
                    'rating' => $validatedData['rating'],
                    'comment' => $validatedData['comment'] ?? null,
                ]
            );

            DB::commit();

            $action = $rating->wasRecentlyCreated ? 'تم إضافة التقييم بنجاح!' : 'تم تحديث التقييم بنجاح!';

            return response()->json([
                'success' => true,
                'data' => $rating,
                'message' => $action
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ التقييم',
            ], 500);
        }
    }


    public function index(Request $request)
    {
        try {
            $page = $request->get('page', 1); // الحصول على رقم الصفحة من Request
            $perPage = 5; // تعليق واحد لكل صفحة

            $ratings = PublicRating::with('user:id,name,email,image')
                ->where('is_visible', true)
                ->orderBy('rating', 'DESC')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['id', 'user_id', 'rating', 'comment', 'created_at'], 'page', $page);

            // إذا كانت الصفحة المطلوبة أكبر من عدد الصفحات المتاحة
            if ($page > $ratings->lastPage() && $ratings->total() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد تعليقات في هذه الصفحة'
                ], 404);
            }

            $ratings->getCollection()->transform(function ($rating) {
                if ($rating->user && $rating->user->image) {
                    // إضافة URL كامل للصورة
                    $rating->user->image_url = asset('uploads/' . $rating->user->image);
                    // 'image' => $user->image ? asset('uploads/' . $user->image) : null,
                }
                return $rating;
            });

            return response()->json([
                'success' => true,
                'data' => $ratings->items(),
                'pagination' => [
                    'current_page' => $ratings->currentPage(),
                    'last_page' => $ratings->lastPage(),
                    'per_page' => $ratings->perPage(),
                    'total' => $ratings->total(),
                    'has_more_pages' => $ratings->hasMorePages(),
                    'next_page_url' => $ratings->nextPageUrl(),
                    'prev_page_url' => $ratings->previousPageUrl(),
                ],
                'stats' => PublicRating::getRatingStats()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب التقييمات',
            ], 500);
        }
    }












}



