<?php

namespace App\Http\Controllers;

use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        try {
            $branches = Branch::orderBy('created_at')->get();

            return response()->json([
                'success' => true,
                'data' => BranchResource::collection($branches),
                'message' => 'تم جلب الفروع بنجاح'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الفروع',
            ], 500);
        }
    }
}
