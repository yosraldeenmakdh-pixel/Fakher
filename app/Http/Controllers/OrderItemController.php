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


    // public function store(Request $request)
    // {
    //     try {
    //         DB::beginTransaction();

    //         $validated = $request->validate([
    //             'meal_id' => 'required|exists:meals,id',
    //             'quantity' => 'required|integer|min:1',
    //             'unit_price' => 'required|numeric|min:0',
    //         ]);

    //         $user = Auth::user() ;
    //         $order = $user->orders_online()->where('status','collecting')->first() ;


    //         if($order){
    //             $validated['order_online_id'] = $order['id'] ;

    //         }
    //         else{
    //             $newOrder = OrderOnline::create([
    //                 'order_number'=>OrderOnline::generateOrderNumber() ,
    //                 'user_id'=>$user->id
    //             ]) ;

    //             $validated['order_online_id'] = $newOrder['id'] ;
    //         }



    //         $validated['total_price'] = $validated['quantity'] * $validated['unit_price'];

    //         $orderItem = OrderOnlineItem::create($validated);

    //         $this->updateOrderTotal($validated['order_online_id']);

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'تم إضافة العنصر للطلب بنجاح'
    //         ], 201);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }


    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'meal_id' => 'required|exists:meals,id',
                'quantity' => 'required|integer|min:1',
                'unit_price' => 'required|numeric|min:0',
            ]);

            $user = Auth::user();
            $order = $user->orders_online()->where('status', 'collecting')->first();

            if (!$order) {
                $order = OrderOnline::create([
                    'order_number' => OrderOnline::generateOrderNumber(),
                    'user_id' => $user->id
                ]);
            }

            // التحقق إذا كانت الوجبة موجودة مسبقاً في الطلب
            $existingItem = OrderOnlineItem::where('order_online_id', $order->id)
                ->where('meal_id', $validated['meal_id'])
                ->first();

            if ($existingItem) {
                // تحديث الكمية والسعر للعنصر الموجود
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $validated['quantity'],
                    'total_price' => ($existingItem->quantity + $validated['quantity']) * $validated['unit_price'],
                    'unit_price' => $validated['unit_price'] // تحديث سعر الوحدة أيضاً إذا تغير
                ]);
            } else {
                // إنشاء عنصر جديد إذا لم يكن موجوداً
                $validated['order_online_id'] = $order->id;
                $validated['total_price'] = $validated['quantity'] * $validated['unit_price'];
                OrderOnlineItem::create($validated);
            }

            $this->updateOrderTotal($order->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $existingItem ? 'تم تحديث العنصر في الطلب بنجاح' : 'تم إضافة العنصر للطلب بنجاح'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    /**
 * حذف عنصر من سلة المستخدم
 */
public function removeItem(Request $request)
{
    try {
        $request->validate([
            'item_id' => 'required|integer|exists:order_online_items,id',
        ], [
            'item_id.required' => 'معرف الوجبة مطلوب',
            'item_id.integer' => 'معرف الوجبة يجب أن يكون رقماً',
            'item_id.exists' => 'الوجبة غير موجودة'
        ]);
        DB::beginTransaction();

        $user = Auth::user();

        $item_id = $request->item_id ;

        // البحث عن الطلب pending للمستخدم
        $order = $user->orders_online()
            ->where('status', 'collecting')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد سلة تسوق فعالة'
            ], 404);
        }

        // البحث عن العنصر المراد حذفه والتأكد أنه ينتمي للطلب
        $orderItem = $order->items()
            ->where('id', $item_id)
            ->first();

        if (!$orderItem) {
            return response()->json([
                'success' => false,
                'message' => 'العنصر غير موجود في السلة'
            ], 404);
        }

        // حفظ سعر العنصر قبل الحذف لتحديث الإجمالي
        $itemTotalPrice = $orderItem->total_price;

        // حذف العنصر
        $orderItem->delete();

        // التحقق إذا كانت السلة أصبحت فارغة
        $remainingItems = $order->items()->count();

        if ($remainingItems === 0) {
            // إذا كانت السلة فارغة، نحذف الطلب بالكامل
            $order->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف العنصر والسلة أصبحت فارغة',
                'data' => [
                    'cart_empty' => true,
                    'remaining_items' => 0
                ]
            ]);
        }

        // تحديث إجمالي الطلب
        $this->updateOrderTotal($order->id);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف العنصر من السلة بنجاح',
            'data' => [
                'cart_empty' => false,
                'remaining_items' => $remainingItems,
                'deleted_item' => [
                    'id' => $item_id,
                    'total_price' => $itemTotalPrice
                ]
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'فشل في حذف العنصر من السلة'
        ], 500);
    }
}


}
