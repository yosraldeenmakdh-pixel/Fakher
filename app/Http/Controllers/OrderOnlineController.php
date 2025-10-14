<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderOnlineRequest;
use App\Models\OrderOnline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderOnlineController extends Controller
{



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
