<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderOnline;
use App\Models\OrderOnlineItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderItemController extends Controller
{


    private function updateOrderTotal($orderId)
    {
        $total = OrderOnlineItem::where('order_online_id', $orderId)->sum('total_price');

        OrderOnline::where('id', $orderId)->update([
            'total' => $total
        ]);
    }


    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'meal_id' => 'required|exists:meals,id',
                'quantity' => 'required|integer|min:1',
                'unit_price' => 'required|numeric|min:0',
            ]);

            $user = Auth::user() ;
            $order = $user->orders_online()->where('status','pending')->first() ;


            if($order){
                $validated['order_online_id'] = $order['id'] ;

            }
            else{
                $newOrder = OrderOnline::create([
                    'order_number'=>OrderOnline::generateOrderNumber() ,
                    'user_id'=>$user->id
                ]) ;

                $validated['order_online_id'] = $newOrder['id'] ;
            }



            $validated['total_price'] = $validated['quantity'] * $validated['unit_price'];

            $orderItem = OrderOnlineItem::create($validated);

            $this->updateOrderTotal($validated['order_online_id']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة العنصر للطلب بنجاح'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


}
