<?php

namespace App\Http\Controllers;

// use App\Filament\Resources\Offers\OfferResource;

use App\Http\Resources\OfferResource;
use App\Models\Offer;
use Illuminate\Http\Request;

class OfferController extends Controller
{

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 6);
            $activeOnly = $request->get('active_only', false);

            $query = Offer::query();


            $query->where('is_active', true);


            $query->orderBy('created_at', 'desc');

            $offers = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => OfferResource::collection($offers),
                'meta' => [
                    'current_page' => $offers->currentPage(),
                    'last_page' => $offers->lastPage(),
                    'per_page' => $offers->perPage(),
                    'total' => $offers->total(),
                    'from' => $offers->firstItem(),
                    'to' => $offers->lastItem(),
                ],
                'links' => [
                    'first' => $offers->url(1),
                    'last' => $offers->url($offers->lastPage()),
                    'prev' => $offers->previousPageUrl(),
                    'next' => $offers->nextPageUrl(),
                ],
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب العروض',
            ], 500);
        }
    }

}
