<?php

namespace App\Http\Controllers;

use App\Models\Kitchen;
use Illuminate\Http\Request;

class KitchenController extends Controller
{

    public function index(Request $request)
{
    try {
        // بناء الاستعلام الأساسي
        $query = Kitchen::with(['user:id,name', 'branch:id,name'])
            ->select([
                'id',
                'user_id',
                'branch_id',
                'name',
                'description',
                'contact_phone',
                'contact_email',
                'address',
                'opening_time',
                'closing_time',
                'is_active',
                'Financial_debts',
                'created_at',
                'updated_at'
            ]);

        if ($request->has('branch_id') && $request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $kitchens = $query->orderBy('name', 'asc')->get();


        $transformedKitchens = $kitchens->map(function ($kitchen) {
            return [
                'id' => $kitchen->id,
                'name' => $kitchen->name,
                'description' => $kitchen->description,
                'contact_phone' => $kitchen->contact_phone,
                'contact_email' => $kitchen->contact_email,
                'address' => $kitchen->address,
                'opening_time' => $kitchen->opening_time,
                'closing_time' => $kitchen->closing_time,
                'is_active' => $kitchen->is_active,
                'branch' => $kitchen->branch ? [
                    'id' => $kitchen->branch->id,
                    'name' => $kitchen->branch->name,
                ] : null,
                'created_at' => $kitchen->created_at,
                'updated_at' => $kitchen->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformedKitchens,
            'count' => $kitchens->count()
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء جلب البيانات',
        ], 500);
    }
}

}
