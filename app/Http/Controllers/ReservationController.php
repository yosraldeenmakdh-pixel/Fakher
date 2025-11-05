<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    // دالة إنشاء الحجز الجديدة التي تعتمد على checkAvailability
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'guests_count' => 'required|integer|min:1',
            'arrival_time' => 'required|date|after:now',
            'departure_time' => 'required|date|after:arrival_time',
            'notes' => 'nullable|string|max:500',
        ], [
            'customer_name.required' => 'اسم العميل مطلوب',
            'customer_phone.required' => 'رقم الهاتف مطلوب',
            'arrival_time.after' => 'وقت الوصول يجب أن يكون في المستقبل',
            'departure_time.after' => 'وقت المغادرة يجب أن يكون بعد وقت الوصول',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();
            $user = Auth::user();

            // استخدام دالة checkAvailability للتحقق من التوافر أولاً
            $availabilityCheck = $this->checkAvailabilityInternal(
                $data['arrival_time'],
                $data['departure_time'],
                $data['guests_count']
            );

            // إذا لم يكن الوقت متاحاً، نرجع الاقتراحات الذكية
            if (!$availabilityCheck['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن إنشاء الحجز - الوقت غير متاح',
                    'data' => $availabilityCheck['data']
                ], 409);
            }

            DB::beginTransaction();

            // الحصول على الطاولة المختارة تلقائياً
            $selectedTable = $availabilityCheck['data']['selected_table'];
            $cleaningTime = 30;

            $arrivalTime = Carbon::parse($data['arrival_time']);
            $departureTime = Carbon::parse($data['departure_time']);
            $actualDepartureTime = $departureTime->copy()->addMinutes($cleaningTime);

            // إنشاء الحجز
            $reservation = Reservation::create([
                'table_id' => $selectedTable['table_id'],
                'user_id' => $user->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'guests_count' => $data['guests_count'],
                'arrival_time' => $arrivalTime,
                'departure_time' => $departureTime, // حفظ الوقت الإجمالي مع التنظيف
                'actual_meal_end' => $actualDepartureTime, // حفظ الوقت الإجمالي مع التنظيف
                'status' => 'checked',
                'notes' => $data['notes'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الحجز بنجاح',
                'data' => [
                    'reservation' => [
                        'id' => $reservation->id,
                        'customer_name' => $reservation->customer_name,
                        'customer_phone' => $reservation->customer_phone,
                        'guests_count' => $reservation->guests_count,
                        'arrival_time' => $reservation->arrival_time->format('Y-m-d H:i:s'),
                        'departure_time' => $reservation->departure_time->format('Y-m-d H:i:s'),
                        'status' => $reservation->status,
                        'notes' => $reservation->notes,
                        'table_info' => [
                            'id' => $selectedTable['table_id'],
                            'name' => $selectedTable['table_name'],
                            'capacity' => $selectedTable['capacity']
                        ]
                    ],
                    'time_details' => $availabilityCheck['data']['time_details']
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الحجز',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // دالة التحقق من التوافر الداخلية (بدون response)
    private function checkAvailabilityInternal($arrivalTime, $departureTime, $guestsCount)
    {
        $arrivalTime = Carbon::parse($arrivalTime);
        $departureTime = Carbon::parse($departureTime);

        // البحث عن الطاولات المناسبة
        $suitableTables = Table::where('capacity', '>=', $guestsCount)
            ->where('status', 'available')
            ->get();

        if ($suitableTables->isEmpty()) {
            return [
                'success' => false,
                'data' => [
                    'available' => false,
                    'requested_guests' => $guestsCount,
                    'suggested_times' => [],
                    'available_tables_count' => 0,
                    'message' => 'لا توجد طاولات مناسبة لعدد الضيوف المطلوب'
                ]
            ];
        }

        $availableTables = [];
        $cleaningTime = 30;
        $actualDepartureTime = $departureTime->copy()->addMinutes($cleaningTime);

        // التحقق من توفر كل طاولة مناسبة
        foreach ($suitableTables as $table) {
            $isAvailable = $this->checkTableAvailability(
                $table->id,
                $arrivalTime,
                $actualDepartureTime
            );

            if ($isAvailable) {
                $availableTables[] = [
                    'table_id' => $table->id,
                    'table_name' => $table->name,
                    'capacity' => $table->capacity,
                    'perfect_fit' => $table->capacity == $guestsCount
                ];
            }
        }

        // إذا وجدنا طاولات متاحة
        if (!empty($availableTables)) {
            // نفضل الطاولة التي تناسب العدد تماماً
            usort($availableTables, function($a, $b) use ($guestsCount) {
                if ($a['perfect_fit'] && !$b['perfect_fit']) return -1;
                if (!$a['perfect_fit'] && $b['perfect_fit']) return 1;
                return $a['capacity'] - $b['capacity'];
            });

            $selectedTable = $availableTables[0];

            return [
                'success' => true,
                'data' => [
                    'available' => true,
                    'selected_table' => $selectedTable,
                    'available_tables_count' => count($availableTables),
                    'all_available_tables' => $availableTables,
                    'time_details' => [
                        'arrival_time' => $arrivalTime->format('Y-m-d H:i:s'),
                        'departure_time' => $departureTime->format('Y-m-d H:i:s'),
                        'actual_departure_time' => $actualDepartureTime->format('Y-m-d H:i:s'),
                        'meal_duration_minutes' => $arrivalTime->diffInMinutes($departureTime),
                        'cleaning_time_minutes' => $cleaningTime,
                        'total_duration_minutes' => $arrivalTime->diffInMinutes($actualDepartureTime)
                    ],
                    'guests_count' => $guestsCount
                ]
            ];
        }

        // إذا لم توجد طاولات متاحة، نبحث عن أوقات مقترحة
        $suggestedTimes = $this->getSuggestedTimes($guestsCount, $arrivalTime, $departureTime);

        return [
            'success' => false,
            'data' => [
                'available' => false,
                'requested_guests' => $guestsCount,
                'suggested_times' => $suggestedTimes,
                'available_tables_count' => 0,
                'message' => 'لا توجد طاولات متاحة في الوقت المطلوب'
            ]
        ];
    }

    // دالة التحقق من التوافر العامة (للاستخدام المباشر عبر API)
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'arrival_time' => 'required|date|after:now',
            'departure_time' => 'required|date|after:arrival_time',
            'guests_count' => 'required|integer|min:1',
        ], [
            'arrival_time.after' => 'وقت الوصول يجب أن يكون في المستقبل',
            'departure_time.after' => 'وقت المغادرة يجب أن يكون بعد وقت الوصول',
            'guests_count.min' => 'عدد الضيوف يجب أن يكون على الأقل 1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();

            $availabilityResult = $this->checkAvailabilityInternal(
                $data['arrival_time'],
                $data['departure_time'],
                $data['guests_count']
            );

            if ($availabilityResult['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم العثور على طاولات متاحة',
                    'data' => $availabilityResult['data']
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $availabilityResult['data']['message'],
                    'data' => $availabilityResult['data']
                ], 409);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التحقق من التوافر',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // باقي الدوال المساعدة تبقى كما هي (checkTableAvailability, getSuggestedTimes, findAvailableTable)
    private function checkTableAvailability($tableId, $arrivalTime, $departureTime)
    {
        $cleaningTime = 30;

        $conflictingReservations = Reservation::where('table_id', $tableId)
            ->whereIn('status', ['checked','confirmed'])
            ->where(function ($query) use ($arrivalTime, $departureTime, $cleaningTime) {
                $query->where(function ($q) use ($arrivalTime, $departureTime) {
                    $q->whereBetween('arrival_time', [$arrivalTime, $departureTime])
                      ->orWhereBetween('departure_time', [$arrivalTime, $departureTime])
                      ->orWhere(function ($q) use ($arrivalTime, $departureTime) {
                          $q->where('arrival_time', '<', $arrivalTime)
                            ->where('departure_time', '>', $departureTime);
                      });
                })
                ->orWhere(function ($q) use ($arrivalTime, $departureTime, $cleaningTime) {
                    $q->where('departure_time', '>', $arrivalTime->copy()->subMinutes($cleaningTime))
                      ->where('departure_time', '<=', $arrivalTime);
                })
                ->orWhere(function ($q) use ($arrivalTime, $departureTime, $cleaningTime) {
                    $q->where('arrival_time', '>=', $departureTime)
                      ->where('arrival_time', '<', $departureTime->copy()->addMinutes($cleaningTime));
                });
            })
            ->exists();

        return !$conflictingReservations;
    }

    private function getSuggestedTimes($guestsCount, $requestedArrival, $requestedDeparture)
    {
        $duration = $requestedArrival->diffInMinutes($requestedDeparture);
        $cleaningTime = 30;
        $suggestions = [];

        $timeSuggestions = [
            ['hours' => -1, 'label' => 'قبل الوقت المطلوب بساعة', 'priority' => 1],
            ['hours' => 1, 'label' => 'بعد الوقت المطلوب بساعة', 'priority' => 2],
            ['hours' => -2, 'label' => 'قبل الوقت المطلوب بساعتين', 'priority' => 3],
            ['hours' => 2, 'label' => 'بعد الوقت المطلوب بساعتين', 'priority' => 4],
            ['hours' => -3, 'label' => 'قبل الوقت المطلوب بثلاث ساعات', 'priority' => 5],
            ['hours' => 3, 'label' => 'بعد الوقت المطلوب بثلاث ساعات', 'priority' => 6],
        ];

        foreach ($timeSuggestions as $suggestion) {
            $newArrival = $requestedArrival->copy()->addHours($suggestion['hours']);
            $newDeparture = $newArrival->copy()->addMinutes($duration);
            $newActualDeparture = $newDeparture->copy()->addMinutes($cleaningTime);

            if ($newArrival->isPast()) {
                continue;
            }

            $availableTable = $this->findAvailableTable($guestsCount, $newArrival, $newActualDeparture);

            if ($availableTable) {
                $suggestions[] = [
                    'arrival_time' => $newArrival->format('Y-m-d H:i:s'),
                    'departure_time' => $newDeparture->format('Y-m-d H:i:s'),
                    'actual_departure_time' => $newActualDeparture->format('Y-m-d H:i:s'),
                    'type' => $suggestion['label'],
                    'table_available' => true,
                    'table_info' => $availableTable,
                    'priority' => $suggestion['priority']
                ];
            }
        }

        usort($suggestions, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });

        return $suggestions;
    }

    private function findAvailableTable($guestsCount, $arrivalTime, $departureTime)
    {
        $suitableTables = Table::where('capacity', '>=', $guestsCount)
            ->where('status', 'available')
            ->get();

        foreach ($suitableTables as $table) {
            $isAvailable = $this->checkTableAvailability(
                $table->id,
                $arrivalTime,
                $departureTime
            );

            if ($isAvailable) {
                return [
                    'table_id' => $table->id,
                    'table_name' => $table->name,
                    'capacity' => $table->capacity,
                    'perfect_fit' => $table->capacity == $guestsCount
                ];
            }
        }

        return null;
    }


public function getUserReservations(Request $request)
{
    try {
        $user = Auth::user();

        // الحصول على الحجوزات المؤكدة فقط للمستخدم الحالي
        // $reservations = Reservation::where('user_id', $user->id)
        //     ->whereIn('status', ['checked','confirmed'])
        //     ->with(['table'])
        //     ->orderBy('arrival_time', 'asc')
        //     ->get();

        $reservations = Reservation::where('user_id', $user->id)
            ->whereIn('status', ['checked', 'confirmed'])
            ->with(['table'])
            ->orderByRaw("FIELD(status, 'checked', 'confirmed')") // checked أولاً
            ->orderBy('arrival_time', 'asc') // ثم حسب وقت الوصول
            ->get();

        if ($reservations->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'لا توجد حجوزات مؤكدة',
                'data' => [
                    'reservations' => [],
                    'stats' => [
                        'total_reservations' => 0,
                        'upcoming_reservations' => 0,
                        'past_reservations' => 0
                    ]
                ]
            ], 200);
        }

        // إحصائيات إضافية
        $now = now();
        $upcomingReservations = $reservations->where('arrival_time', '>', $now);
        $pastReservations = $reservations->where('arrival_time', '<=', $now);

        // تنسيق البيانات للإرجاع
        $formattedReservations = $reservations->map(function ($reservation) use ($now) {
            $arrivalTime = $reservation->arrival_time;
            $isUpcoming = $arrivalTime > $now;
            $timeStatus = $isUpcoming ? 'قادم' : 'منتهي';

            // حساب الوقت المتبقي للحجوزات القادمة
            $timeRemaining = null;
            if ($isUpcoming) {
                $timeRemaining = [
                    // 'days' => $now->diffInDays($arrivalTime),
                    'hours' => $now->diffInHours($arrivalTime) % 24,
                    'minutes' => $now->diffInMinutes($arrivalTime) % 60
                ];
            }

            return [
                'id' => $reservation->id,
                'customer_name' => $reservation->customer_name,
                'customer_phone' => $reservation->customer_phone,
                'guests_count' => $reservation->guests_count,
                'arrival_time' => $arrivalTime->format('Y-m-d H:i:s'),
                'departure_time' => $reservation->departure_time->format('Y-m-d H:i:s'),
                'actual_meal_end' => $reservation->actual_meal_end ,
                'status' => $reservation->status,
                'notes' => $reservation->notes,
                'time_status' => $timeStatus,
                'time_remaining' => $timeRemaining,
                'table_info' => [
                    // 'id' => $reservation->table->id,
                    'name' => $reservation->table->name,
                    'capacity' => $reservation->table->capacity,
                    // 'location' => $reservation->table->location ?? 'غير محدد'
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'تم جلب الحجوزات بنجاح',
            'data' => [
                'reservations' => $formattedReservations,
                'stats' => [
                    'total_reservations' => $reservations->count(),
                    'upcoming_reservations' => $upcomingReservations->count(),
                    // 'past_reservations' => $pastReservations->count()
                ]
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء جلب الحجوزات',
            'error' => $e->getMessage()
        ], 500);
    }

}

public function cancelReservation(Request $request, $id)
    {
        try {
            $user = Auth::user();

            // البحث عن الحجز
            $reservation = Reservation::where('id', $id)
                ->where('user_id', $user->id) // التأكد من أن المستخدم هو صاحب الحجز
                ->first();

            if (!$reservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'الحجز غير موجود أو لا تملك صلاحية لإلغائه'
                ], 404);
            }

            $now = now();
            $timeUntilArrival = $now->diffInMinutes($reservation->arrival_time, false);

            if ($timeUntilArrival < 60) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن إلغاء الحجز قبل وقت الوصول بأقل من 60 دقيقة',
                    'data' => [
                        'arrival_time' => $reservation->arrival_time->format('Y-m-d H:i:s'),
                        'time_until_arrival_minutes' => $timeUntilArrival,
                        'minimum_cancellation_time' => 30
                    ]
                ], 422);
            }

            DB::beginTransaction();

            // حفظ الحالة القديمة للإرجاع
            $oldStatus = $reservation->status;

            $reservation->update([
                'status' => 'cancelled',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء الحجز بنجاح',
                'data' => [
                    'reservation' => [
                        'id' => $reservation->id,
                        'customer_name' => $reservation->customer_name,
                        'arrival_time' => $reservation->arrival_time->format('Y-m-d H:i:s'),
                        'departure_time' => $reservation->departure_time->format('Y-m-d H:i:s'),
                        'old_status' => $oldStatus,
                        // 'old_status_arabic' => $this->getStatusArabic($oldStatus),
                        'new_status' => $reservation->status,
                        // 'new_status_arabic' => $this->getStatusArabic($reservation->status),
                        'cancelled_at' => now()->format('Y-m-d H:i:s')
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إلغاء الحجز',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
