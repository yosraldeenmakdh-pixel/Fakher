<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostListResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function news()
    {
        try {
            $news = Post::where('type', 'news')
                        ->where('is_published', true)
                        ->orderBy('created_at', 'desc')
                        ->get();

            return response()->json([
                'success' => true,
                'data' => PostListResource::collection($news),
                'message' => 'تم جلب الأخبار بنجاح'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الأخبار',
            ], 500);
        }
    }

    /**
     * عرض جميع المقالات (بدون المحتوى الكامل)
     */
    public function articles()
    {
        try {
            $articles = Post::where('type', 'article')
                            ->where('is_published', true)
                            ->orderBy('created_at', 'desc')
                            ->get();

            return response()->json([
                'success' => true,
                'data' => PostListResource::collection($articles),
                'message' => 'تم جلب المقالات بنجاح'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المقالات',
            ], 500);
        }
    }

    public function show(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:posts,id', // تغيير string إلى integer إذا كان ID رقمي
            ], [
                'id.required' => 'معرف المنشور مطلوب',
                'id.integer' => 'معرف المنشور يجب أن يكون رقماً',
                'id.exists' => 'المنشور غير موجود'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صالحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            $post = Post::where('is_published', true)->where('id',$request->id)->first();

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'المنشور غير موجود أو غير منشور'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new PostResource($post),
                'message' => 'تم جلب المنشور بنجاح'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المنشور',
            ], 500);
        }
    }

}
