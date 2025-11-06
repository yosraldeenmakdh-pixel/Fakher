<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderOnlineRequest;
use App\Models\OrderOnline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderOnlineController extends Controller
{


    public function getCart()
    {
        try {
            $user = Auth::user();

            $order = $user->orders_online()
                ->with(['items.meal' => function($query) {
                    $query->select('id', 'name','description','price','image','average_rating');
                }])
                ->where('status', 'pending')
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => true,
                    'message' => 'لا توجد عناصر في السلة',
                    'data' => [
                        'order' => null,
                        'total_items' => 0,
                        'total_price' => 0
                    ]
                ]);
            }

            // حساب الإجماليات
            // $totalItems = $order->items->sum('quantity');
        $totalPrice = $order->items ? $order->items->sum('total_price') : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'created_at' => $order->created_at,
                        'updated_at' => $order->updated_at,
                        'items' => $order->items->map(function($item) {
                            return [
                                'id' => $item->id,
                                'meal_id' => $item->meal_id,
                                'meal_name' => $item->meal->name ?? 'وجبة محذوفة',
                                'meal_description' => $item->meal->description ?? 'وجبة محذوفة',
                                'meal_price' => $item->meal->price ?? 'وجبة محذوفة',
                                'meal_average_rating' => $item->meal->average_rating ?? 'وجبة محذوفة',
                                'meal_image' => $item->meal->image ? asset('uploads/' . $item->meal->image) : null,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price,
                                'total_price' => $item->total_price,
                                'created_at' => $item->created_at
                            ];
                        }),
                        'summary' => [
                            // 'total_items' => $totalItems,
                            'total_price' => $totalPrice,
                            // 'items_count' => $order->items->count()
                        ]
                    ]
                ]
            ]);

        } catch (\Exception $e) {
                return response()->json([
                'success' => false,
                'message' => 'فشل في جلب محتويات السلة'
            ], 500);
        }
    }





















    // public function store(StoreOrderOnlineRequest $request)
    // {
    //     $user = Auth::user() ;
    //     $orderData = $request->validated();
    //     $orderData['order_number'] = OrderOnline::generateOrderNumber();
    //     $orderData['user_id'] = $user->id ;

    //     $order = OrderOnline::create($orderData);

    //     if (isset($orderData['items'])) {
    //         foreach ($orderData['items'] as $item) {
    //             $order->items()->create($item);
    //         }
    //     }
    //     else{
    //         return response()->json([
    //             'message'=>'السلة فارغة لم تطلب أي وجبة'
    //         ], 200);
    //     }


    //     return response()->json([
    //         'message' => 'تم إنشاء الطلب بنجاح',
    //         'order' => new OrderOnlineResource($order->load(['user', 'branch', 'items']))
    //     ], 201);
    // }


}
