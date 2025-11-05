<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstitutionOrder extends Model
{
    protected $fillable = [
        'institution_id',
        'branch_id',
        'order_number',
        'delivery_date',
        'delivery_time',
        'total_amount',
        'status',
        'special_instructions',
        'kitchen_id',
        'confirmed_at',
        'delivered_at'
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'delivery_time' => 'datetime:H:i',
        'total_amount' => 'decimal:2',
    ];




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
               $this->getOriginal('status') === 'Pending' &&
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

            $freshOrder = self::where('id', $this->id)
                ->lockForUpdate()
                ->first();
            try {
                InstitutionOrderConfirmation::create([
                    'order_id' => $this->id,
                    'order_number' => $this->order_number,
                    'delivery_date' => $this->delivery_date,
                    'delivery_time' => $this->delivery_time,
                    'total_amount' => $this->total_amount,
                    'status' => $this->status,
                    'special_instructions' => $this->special_instructions,
                    'kitchen_id' => Auth::user()->kitchen->id,
                    'notes' => 'تم تأكيد الطلب وبدء التحضير',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->kitchen_id = Auth::user()->kitchen->id;
                $this->confirmed_at = now();
                $this->saveQuietly();


            } catch (\Exception $e) {
                throw $e ;
            }

        });
    }


    public function institution()
    {
        return $this->belongsTo(OfficialInstitution::class, 'institution_id');
    }

    /**
     * العلاقة مع الفرع
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * العلاقة مع عناصر الطلب
     */
    public function orderItems()
    {
        return $this->hasMany(InstitutionOrderItem::class);
    }

    /**
     * الحصول على عدد العناصر في الطلب
     */
    public function getItemsCountAttribute()
    {
        return $this->orderItems()->count();
    }


    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function confirmation()
    {
        return $this->hasOne(InstitutionOrderConfirmation::class, 'order_id');
    }
}
