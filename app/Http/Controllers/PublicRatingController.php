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
             $ratings = PublicRating::with('user:id,name,email,image')
                ->where('is_visible', true)
                // ->orderBy('rating', 'DESC')
                ->orderBy('created_at', 'desc')
                ->get(['id', 'user_id', 'rating', 'comment', 'created_at']);



            $ratings->transform(function ($rating) {
                if ($rating->user && $rating->user->image) {
                    $rating->user->image_url = asset('uploads/' . $rating->user->image);
                }
                return $rating;
            });

            return response()->json([
                'success' => true,
                'data' => $ratings,
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



