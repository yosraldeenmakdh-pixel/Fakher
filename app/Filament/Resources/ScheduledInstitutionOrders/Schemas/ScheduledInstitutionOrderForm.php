<?php

namespace App\Filament\Resources\ScheduledInstitutionOrders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
// use Filament\Forms\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyScheduleMeal;
use App\Models\DailyKitchenSchedule;
use App\Models\ScheduledInstitutionOrder;
use Closure;
use Filament\Actions\Action;
use Filament\Schemas\Components\Grid;

class ScheduledInstitutionOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        $currentInstitution = Auth::user()->officialInstitution;
        $isKitchen = Auth::user()->hasRole('kitchen');

        return $schema
            ->components([

                Section::make('المعلومات الأساسية')
                    ->schema([
                        ...(Auth::user()->hasRole('institution') ? [

                                    Hidden::make('institution_id')
                                        ->default($currentInstitution->id),

                                    Placeholder::make('current_institution')
                                        ->label('المؤسسة')
                                        ->content($currentInstitution->name ?? 'غير معين')
                                        ->extraAttributes(['class' => 'font-bold']),

                                ] : [

                                    Select::make('institution_id')
                                        ->label('المؤسسة')
                                        ->relationship('institution', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->disabled($isKitchen),
                                ]) ,

                                ...(Auth::user()->hasRole('institution') ? [
                                    Hidden::make('branch_id')
                                        ->default($currentInstitution->branch->id),

                                    Placeholder::make('current_branch')
                                        ->label('الفرع')
                                        ->content($currentInstitution->branch->name ?? 'غير معين')
                                        ->extraAttributes(['class' => 'font-bold']),
                                ]:[
                                    Select::make('branch_id')
                                        ->label('الفرع')
                                        ->relationship('branch', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->disabled($isKitchen),
                                ]) ,


                                ...(Auth::user()->hasRole('institution') ? [
                                    Hidden::make('kitchen_id')
                                        ->default($currentInstitution->kitchen->id),

                                    Placeholder::make('current_kitchen')
                                        ->label('المطبخ')
                                        ->content($currentInstitution->kitchen->name ?? 'غير معين')
                                        ->extraAttributes(['class' => 'font-bold']),
                                ]:[
                                    Select::make('kitchen_id')
                                        ->label('المطبخ')
                                        ->relationship('kitchen', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->disabled($isKitchen)
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            self::updateAvailableMeals($set, $get);
                                        }),
                                ]) ,

                        DatePicker::make('order_date')
                            ->label('تاريخ الطلب')
                            ->required()
                            ->native(false)
                            // ->readOnly($isKitchen)
                            ->disabled($isKitchen)
                            ->displayFormat('d/m/Y')
                            ->minDate(now())
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::updateAvailableMeals($set, $get);
                                self::checkExistingOrder($set, $get);
                            })
                            ->suffixAction(
                                Action::make('checkDate')
                                    ->icon('heroicon-o-check')
                                    ->action(function ($set, $get) {
                                        self::updateAvailableMeals($set, $get);
                                        self::checkExistingOrder($set, $get);
                                    })
                            )
                            ->rules([
                                // إضافة قاعدة تحقق مخصصة
                                function (callable $get) {
                                    return function (string $attribute, $value, Closure $fail) use ($get) {
                                        $existingOrder = self::getExistingOrder($get);
                                        if ($existingOrder) {
                                            $fail('⚠️ يوجد طلب سابق في هذا التاريخ. لا يمكنك إنشاء طلب جديد. يمكنك تعديل الطلب الحالي بدلاً من إنشاء طلب جديد.');
                                        }
                                    };
                                },
                            ])
                            // ->helperText(function (callable $get) {
                            //     $existingOrder = self::getExistingOrder($get);
                            //     if ($existingOrder) {
                            //         return '⚠️ يوجد طلب سابق في هذا التاريخ. لا يمكنك إنشاء طلب جديد. يمكنك تعديل الطلب الحالي بدلاً من إنشاء طلب جديد.';
                            //     }
                            //     return 'اختر تاريخ الطلب (التواريخ المستقبلية فقط)';
                            // }),

                    ])->columns(2),

                Section::make('عدد الأشخاص')
                    ->description('حدد عدد الأشخاص لكل وجبة')
                    ->schema([
                        TextInput::make('breakfast_persons')
                            ->label('عدد الأشخاص - الإفطار')
                            ->numeric()
                            ->disabled($isKitchen)
                            ->minValue(0)
                            // ->default(0)
                            ->required(),

                        TextInput::make('lunch_persons')
                            ->label('عدد الأشخاص - الغداء')
                            ->numeric()
                            ->disabled($isKitchen)
                            ->minValue(0)
                            // ->default(0)
                            ->required(),

                        TextInput::make('dinner_persons')
                            ->label('عدد الأشخاص - العشاء')
                            ->numeric()
                            ->disabled($isKitchen)
                            ->minValue(0)
                            // ->default(0)
                            ->required(),
                    ])->columns(3),

                // قسم الوجبات المجدولة المتاحة
                Section::make('الوجبات المجدولة المتاحة')
                    ->schema([
                        Placeholder::make('available_meals_info')
                            ->label('الوجبات المتاحة للتاريخ المحدد')
                            ->content(function (callable $get) {
                                $kitchenId = $get('kitchen_id');
                                $orderDate = $get('order_date');

                                if (!$kitchenId || !$orderDate) {
                                    return 'يرجى اختيار المطبخ وتاريخ الطلب أولاً';
                                }

                                $meals = DailyScheduleMeal::whereHas('schedule', function($query) use ($kitchenId, $orderDate) {
                                    $query->where('kitchen_id', $kitchenId)
                                          ->whereDate('schedule_date', $orderDate);
                                })->with('meal')->get();

                                if ($meals->isEmpty()) {
                                    return '⚠️ لا يوجد جدول وجبات لهذا المطبخ في التاريخ المحدد';
                                }

                                $breakfastMeals = $meals->where('meal_type', 'breakfast');
                                $lunchMeals = $meals->where('meal_type', 'lunch');
                                $dinnerMeals = $meals->where('meal_type', 'dinner');

                                $output = '';

                                if ($breakfastMeals->isNotEmpty()) {
                                    $output .= "🍳 الفطور: " . $breakfastMeals->map(function($meal) {
                                        return $meal->meal->name . " ({$meal->scheduled_price}$)";
                                    })->join('، ') . "\n";
                                }

                                if ($lunchMeals->isNotEmpty()) {
                                    $output .= "🍽️ الغداء: " . $lunchMeals->map(function($meal) {
                                        return $meal->meal->name . " ({$meal->scheduled_price}$)";
                                    })->join('، ') . "\n";
                                }

                                if ($dinnerMeals->isNotEmpty()) {
                                    $output .= "🌙 العشاء: " . $dinnerMeals->map(function($meal) {
                                        return $meal->meal->name . " ({$meal->scheduled_price}$)";
                                    })->join('، ');
                                }

                                return $output;
                            })
                            ->extraAttributes(['class' => 'whitespace-pre-line text-sm bg-gray-50 p-3 rounded']),
                    ])
                    ->visible(fn (callable $get) => $get('kitchen_id') && $get('order_date')),

                // قسم إضافة الوجبات المطلوبة باستخدام Repeater
                Section::make('الوجبات المطلوبة')
                    ->description('حدد الوجبات المطلوبة وكمياتها')
                    ->hidden(Auth::user()->hasRole('institution'))
                    ->schema([
                        Repeater::make('orderMeals')
                            ->relationship('orderMeals')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('daily_schedule_meal_id')
                                            ->label('الوجبة')
                                            ->options(function (callable $get) {
                                                $kitchenId = $get('../../kitchen_id');
                                                $orderDate = $get('../../order_date');

                                                if (!$kitchenId || !$orderDate) {
                                                    return [];
                                                }

                                                return DailyScheduleMeal::whereHas('schedule', function($query) use ($kitchenId, $orderDate) {
                                                    $query->where('kitchen_id', $kitchenId)
                                                          ->whereDate('schedule_date', $orderDate);
                                                })
                                                ->with('meal')
                                                ->get()
                                                ->mapWithKeys(function ($item) {
                                                    $type = match($item->meal_type) {
                                                        'breakfast' => '🍳 فطور',
                                                        'lunch' => '🍽️ غداء',
                                                        'dinner' => '🌙 عشاء',
                                                        default => $item->meal_type
                                                    };
                                                    return [
                                                        $item->id => "{$item->meal->name} ({$type}) - {$item->scheduled_price}$"
                                                    ];
                                                });
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            // ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if ($state) {
                                                    $scheduleMeal = DailyScheduleMeal::find($state);
                                                    if ($scheduleMeal) {
                                                        $set('unit_price', $scheduleMeal->scheduled_price);
                                                        // تحديث المبلغ الإجمالي تلقائياً
                                                        $quantity = $get('quantity') ?? 1;
                                                        $set('total_price', floatval($quantity) * floatval($scheduleMeal->scheduled_price));
                                                    }
                                                }
                                            }),

                                        TextInput::make('quantity')
                                            ->label('الكمية')
                                            ->required()
                                            ->numeric()
                                            ->minValue(1)
                                            ->suffixAction(
                                                Action::make('updateQuantity')
                                                    ->icon('heroicon-o-check')
                                                    ->action(function ($set, $get, $state) {
                                                            $unitPrice = $get('unit_price') ?? 0;
                                                            $set('total_price', floatval($state) * floatval($unitPrice));
                                                        })
                                            ) ,


                                        TextInput::make('unit_price')
                                            ->label('سعر الوحدة')
                                            ->required()
                                            ->numeric()
                                            ->prefix('$')
                                            ->disabled()
                                            ->dehydrated(),

                                        TextInput::make('total_price')
                                            ->label('المبلغ الإجمالي')
                                            ->required()
                                            ->numeric()
                                            ->prefix('$')
                                            ->disabled()
                                            ->dehydrated(),
                                    ]),
                            ])
                            ->columns(1)
                            ->defaultItems(0)
                            ->createItemButtonLabel('إضافة وجبة')
                            // ->deleteItemButtonLabel('حذف الوجبة')
                            ->minItems(0)
                            ->collapsible()
                            ->itemLabel(function (array $state): string {
                                $mealId = $state['daily_schedule_meal_id'] ?? null;
                                $quantity = $state['quantity'] ?? 0;

                                if ($mealId) {
                                    $meal = DailyScheduleMeal::find($mealId);
                                    if ($meal && $meal->meal) {
                                        return $meal->meal->name . ' - ' . $quantity . ' وجبة';
                                    }
                                }

                                return 'وجبة جديدة';
                            })
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::updateTotalAmount($set, $get);
                            }),

                        Placeholder::make('total_calculated_amount')
                            ->label('المبلغ الإجمالي المحسوب')
                            ->content(function (callable $get) {
                                $orderMeals = $get('orderMeals') ?? [];
                                $total = 0;

                                foreach ($orderMeals as $meal) {
                                    $quantity = $meal['quantity'] ?? 0;
                                    $unitPrice = $meal['unit_price'] ?? 0;
                                    $total += floatval($quantity) * floatval($unitPrice);
                                }

                                return number_format($total, 2) . ' $';
                            })
                            ->extraAttributes(['class' => 'text-success-600 font-bold text-lg']),
                    ]),

                Section::make('المعلومات المالية والحالة')
                    ->hidden(Auth::user()->hasRole('institution'))
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('المبلغ الإجمالي النهائي')
                            ->numeric()
                            ->disabled()
                            ->readOnly()
                            ->required()
                            ->prefix('$')
                            ->minValue(0)
                            ->default(0)
                            ->readOnly()
                            ->reactive()
                            ->afterStateHydrated(function ($component, $state, callable $get) {
                                // تحديث المبلغ الإجمالي عند تحميل البيانات
                                $calculatedTotal = self::calculateTotalAmount($get);
                                if ($calculatedTotal > 0) {
                                    $component->state($calculatedTotal);
                                }
                            }),

                        ...(Auth::user()->hasRole('institution') ? [
                                    Hidden::make('status')
                                        ->default('pending'),

                                    Placeholder::make('status_display')
                                        ->label('حالة الطلب')
                                        ->content('قيد الانتظار')
                                        ->extraAttributes(['class' => 'font-bold text-green-600']),
                                ] : [
                                    Select::make('status')
                                        ->label('حالة الطلب')
                                        ->disabled($isKitchen)
                                        ->required()
                                        ->options([
                                            'pending' => 'قيد الانتظار',
                                            'confirmed' => 'مؤكد',
                                            'delivered' => 'تم التسليم',
                                            'cancelled' => 'ملغي',
                                        ])
                                        ->default('pending')
                                        ->native(false),
                                ]),

                        DateTimePicker::make('confirmed_at')
                            ->label('تاريخ التأكيد')
                            ->hidden(Auth::user()->hasRole('institution')|| $isKitchen),

                        DateTimePicker::make('delivered_at')
                            ->label('تاريخ التوصيل')
                            ->hidden(Auth::user()->hasRole('institution')|| $isKitchen),
                    ])->columns(2),

                Section::make('تعليمات خاصة')
                    ->schema([
                        Textarea::make('special_instructions')
                            ->label('التعليمات الخاصة')
                            ->rows(3)
                            ->disabled($isKitchen)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),

            ]);
    }

    /**
     * تحديث الوجبات المتاحة عند تغيير المطبخ أو التاريخ
     */
    private static function updateAvailableMeals(callable $set, callable $get): void
    {
        $kitchenId = $get('kitchen_id');
        $orderDate = $get('order_date');

        if ($kitchenId && $orderDate) {
            // سيتم تحديث الوجبات المتاحة تلقائياً عبر reactive
        }
    }

    /**
     * تحديث المبلغ الإجمالي
     */
    private static function updateTotalAmount(callable $set, callable $get): void
    {
        $totalAmount = self::calculateTotalAmount($get);
        $set('total_amount', $totalAmount);
    }

    /**
     * حساب المبلغ الإجمالي من الوجبات
     */
    private static function calculateTotalAmount(callable $get): float
    {
        $orderMeals = $get('orderMeals') ?? [];
        $total = 0;

        foreach ($orderMeals as $meal) {
            $quantity = $meal['quantity'] ?? 0;
            $unitPrice = $meal['unit_price'] ?? 0;

            // التأكد من أن القيم رقمية قبل الضرب
            $quantity = is_numeric($quantity) ? floatval($quantity) : 0;
            $unitPrice = is_numeric($unitPrice) ? floatval($unitPrice) : 0;

            $total += $quantity * $unitPrice;
        }

        return $total;
    }

    private static function checkExistingOrder(callable $set, callable $get): void
    {
        $existingOrder = self::getExistingOrder($get);

        if ($existingOrder) {
            // يمكن إضافة منطق إضافي هنا إذا لزم الأمر
            // مثلاً تعطيل الحفظ أو عرض تحذير إضافي
        }
    }

    /**
     * الحصول على الطلب الموجود في نفس التاريخ (إن وجد)
     */
    private static function getExistingOrder(callable $get): ?ScheduledInstitutionOrder
    {
        $institutionId = $get('institution_id');
        $branchId = $get('branch_id');
        $kitchenId = $get('kitchen_id');
        $orderDate = $get('order_date');
        $currentRecordId = $get('id'); // معرف السجل الحالي (للتعديل)

        if (!$institutionId || !$branchId || !$kitchenId || !$orderDate) {
            return null;
        }

        $query = ScheduledInstitutionOrder::where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('kitchen_id', $kitchenId)
            ->whereDate('order_date', $orderDate);

        // استبعاد السجل الحالي في حالة التعديل
        if ($currentRecordId) {
            $query->where('id', '!=', $currentRecordId);
        }

        return $query->first();
    }
}
