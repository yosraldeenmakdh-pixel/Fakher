<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderOnlineRequest;
use App\Models\OrderOnline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
                ->where('status', 'collecting')
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




    private function updateBasicFields(OrderOnline $order, array $data): void
    {
        $allowedFields = [
            'branch_id','kitchen_id', 'special_instructions',
            'customer_phone', 'address', 'order_date'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $order->$field = $data[$field];
            }
        }
    }





    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
            'kitchen_id' => 'required|exists:kitchens,id',
            'customer_phone' => 'required|string|digits:10',
            'address' => 'required|string',
            'special_instructions' => 'sometimes|string',
            'order_date' => [
                'required',
                'date',
                'after_or_equal:' . now()->addMinutes(30)->toDateTimeString()
            ]
        ], [
            'order_date.after_or_equal' => 'يجب أن يكون وقت الاستلام بعد 30 دقيقة على الأقل من الوقت الحالي.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // البحث عن الطلب
            $order = OrderOnline::find($id);


            if (!$order) {
                throw new \Exception('الطلب غير موجود', 404);
            }

            if($order->user_id != Auth::id()){
                throw new \Exception('لا يمكنك تعديل طلب لشخص أخر', 403);
            }


            // حفظ الحالة القديمة
            $oldStatus = $order->status;

            if($oldStatus != 'collecting'){
                throw new \Exception('لا يمكن تعديل الطلب في حالته الحالية', 422);
            }

            // تحديث الحقول الأساسية
            $this->updateBasicFields($order, $request->all());

            $order->customer_name = Auth::user()->name ;
            $order->status = 'pending' ;

            // حفظ التغييرات
            $order->save();


            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الطلب بنجاح',
                'data' => $order->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }



    public function myOrders(Request $request)
    {
        try {
            $user = Auth::user();

            // الحصول على الطلبات مع تحميل جميع العلاقات
            $orders = OrderOnline::where('user_id', $user->id)
                ->with([
                    'branch:id,name',
                    'kitchen:id,name',
                    'items.meal:id,name,image', // تحميل العناصر والوجبات المرتبطة
                    'items' => function($query) {
                        $query->select([
                            'id',
                            'order_online_id',
                            'meal_id',
                            'quantity',
                            'unit_price',
                            'total_price',
                        ]);
                    }
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            // تقسيم الطلبات حسب الحالة
            $groupedOrders = [
                'pending' => $orders->where('status', 'pending')->values(),
                'confirmed' => $orders->where('status', 'confirmed')->values(),
                'delivered' => $orders->where('status', 'delivered')->values(),
            ];

            // إحصائيات
            $stats = [
                'total' => $orders->count(),
                'pending' => $groupedOrders['pending']->count(),
                'confirmed' => $groupedOrders['confirmed']->count(),
                'delivered' => $groupedOrders['delivered']->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => $groupedOrders,
                    'stats' => $stats
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب الطلبات: ' . $e->getMessage()
            ], 500);
        }
    }







    public function custom_update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'sometimes|required|exists:branches,id',
            'customer_phone' => 'sometimes|required|string|digits:10',
            'address' => 'sometimes|required|string',
            'special_instructions' => 'nullable|string',
            'order_date' => [
                'sometimes',
                'required',
                'date',
                'after_or_equal:' . now()->addMinutes(30)->toDateTimeString()
            ]
        ], [
            'order_date.after_or_equal' => 'يجب أن يكون وقت الاستلام بعد 30 دقيقة على الأقل من الوقت الحالي.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user = Auth::user();

            // البحث عن الطلب مع العلاقات
            $order = OrderOnline::with(['items'])->find($id);

            if (!$order) {
                throw new \Exception('الطلب غير موجود', 404);
            }

            if($order->user_id != $user->id){
                throw new \Exception('لا يمكنك تعديل طلب لشخص آخر', 403);
            }

            // التحقق من إمكانية التعديل (فقط الطلبات في حالة collecting)
            if ($order->status !== 'pending') {
                throw new \Exception('لا يمكن تعديل الطلب في حالته الحالية', 422);
            }

            $this->updateBasicFields($order, $request->all());

            $order->save();



            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الطلب بنجاح',
                'data' => $order->fresh(['branch:id,name', 'items.meal:id,name,image'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }







    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            // البحث عن الطلب
            $order = OrderOnline::find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطلب غير موجود'
                ], 404);
            }

            // التحقق من ملكية الطلب
            if ($order->user_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكنك حذف طلب لشخص آخر'
                ], 403);
            }

            // التحقق من إمكانية الحذف (فقط الطلبات في حالة collecting)
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف الطلب في حالته الحالية'
                ], 422);
            }

            $orderNumber = $order->order_number;

            // حذف العناصر المرتبطة أولاً إذا كان لديك علاقة
            if ($order->items) {
                $order->items()->delete();
            }

            // حذف الطلب
            $order->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الطلب بنجاح',
                'data' => [
                    'deleted_order_number' => $orderNumber
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'فشل في حذف الطلب: '
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
