<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderOnline extends Model
{

    protected $table = 'order_onlines';

    protected $fillable = [
        'order_number',
        'user_id',
        'branch_id',
        'kitchen_id',
        'total',
        'status',
        'order_date',
        'confirmed_at',
        'delivered_at',
        'special_instructions',
        'customer_name',
        'customer_phone',
        'address' ,
        'confirmed_by' ,
        'latitude',
        'longitude',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by'); // تحديد المفتاح الخارجي
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function items()
    {
        return $this->hasMany(OrderOnlineItem::class , 'order_online_id');
    }

    public static function generateOrderNumber()
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));

        return "{$prefix}-{$date}-{$random}";
    }


    protected static function boot()
    {
        parent::boot();

        static::updated(function ($model) {
            $model->recordOrderConfirmation();
        });
    }

    public function shouldRecordConfirmation(): bool
    {
        return $this->wasChanged('status') &&
               $this->getOriginal('status') === 'pending' &&
               $this->status === 'confirmed' &&
               Auth::user() &&
               Auth::user()->hasRole('kitchen');
    }

    public function recordOrderConfirmation()
    {
        if (!$this->shouldRecordConfirmation()) {
            return false;
        }

        return DB::transaction(function () {
            try {
                $this->load(['items.meal']);

                $orderItems = $this->items->map(function ($item) {
                    return [
                        'meal_name' => $item->meal->name ?? 'وجبة غير معروفة',
                        'quantity' => $item->quantity,
                    ];
                })->toArray();

                $orderItemsJson = json_encode($orderItems, JSON_UNESCAPED_UNICODE);

                OnlineOrderConfirmation::create([
                    'order_id' => $this->id,
                    'order_number' => $this->order_number,
                    'delivery_date' => $this->order_date,
                    'total_amount' => $this->total,
                    'order_items' => $orderItemsJson,
                    'status' => $this->status,
                    'special_instructions' => $this->special_instructions,
                    'kitchen_id' => $this->kitchen_id,
                    'notes' => 'تم تأكيد الطلب وبدء التحضير',
                ]);

                // $this->kitchen_id = Auth::user()->kitchen->id;
                $this->confirmed_at = now();
                $this->saveQuietly();

            } catch (\Exception $e) {
                throw $e;
            }
        });
    }

}
