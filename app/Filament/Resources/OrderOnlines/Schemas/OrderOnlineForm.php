<?php

namespace App\Filament\Resources\OrderOnlines\Schemas;

use App\Models\Meal;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class OrderOnlineForm
{
    public static function configure(Schema $schema): Schema
    {
        $isKitchen = Auth::user()->hasRole('kitchen');

        return $schema
            ->components([
                Section::make('معلومات الطلب الأساسية')
                    ->schema([
                        TextInput::make('order_number')
                            ->label('رقم الطلب')
                            ->required()
                            ->default('ORD-' . date('Ymd-His'))
                            ->disabled()
                            ->unique(ignoreRecord: true)
                            ->dehydrated(),

                        Select::make('branch_id')
                            ->label('الفرع')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->disabled($isKitchen)
                            ->required(),

                        Select::make('kitchen_id')
                            ->label('المطبخ المسؤول')
                            ->relationship('kitchen', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->hidden($isKitchen)
                            ->nullable(),

                        DateTimePicker::make('order_date')
                            ->label('تاريخ الطلب')
                            ->required()
                            ->disabled($isKitchen)
                            ->default(now()) ,

                        // Select::make('status')
                        //     ->label('حالة الطلب')
                        //     ->options([
                        //         'collecting' => 'جمع الطلب',
                        //         'pending' => 'قيد الانتظار',
                        //         'delivered' => 'تم التوصيل',
                        //         'cancelled' => 'ملغي',
                        //     ])
                        //     ->required()
                        //     ->default('collecting'),


                        Select::make('status')
                            ->label('حالة الطلب')
                            ->required()
                            ->options(function ($get, $set) {
                                $currentStatus = $get('status') ?? 'pending';

                                $options = [
                                    // 'collecting' => 'جمع الطلب',
                                    'pending' => 'قيد الانتظار',
                                    'confirmed' => 'مؤكد',
                                    'delivered' => 'تم التوصيل',
                                    'cancelled' => 'ملغي',
                                ];
                                if(Auth::user()->hasRole('kitchen')){
                                    if ($currentStatus === 'pending') {
                                        // من pending يمكن الانتقال إلى confirmed أو cancelled فقط
                                        unset($options['delivered']);
                                        // unset($options['Pending']);
                                        unset($options['cancelled']);
                                    } elseif ($currentStatus === 'confirmed') {
                                        // من confirmed يمكن الانتقال إلى delivered أو cancelled فقط
                                        unset($options['Pending']);
                                    } elseif (in_array($currentStatus, ['delivered', 'cancelled'])) {
                                        // لا يمكن تغيير الحالة إذا كانت delivered أو cancelled
                                        return [];
                                    }
                                }

                                return $options;
                            })

                            ->default('pending')
                            ->native(false),

                    ])->columns(2),


                Section::make('تفاصيل الوجبات')
                    ->schema([
                         Repeater::make('orderItems')
                            ->relationship('items')
                            ->label('الوجبات المطلوبة')
                            ->disabled($isKitchen)
                            ->schema([
                                Select::make('meal_id')
                                    ->label('الوجبة')
                                    ->options(Meal::where('is_available', true)->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    // ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if ($state) {
                                            $meal = Meal::find($state);
                                            if ($meal) {
                                                $set('unit_price', $meal->price);
                                                $quantity = (int)($get('quantity') ?? 1);
                                                $totalPrice = $quantity * $meal->price;
                                                $set('total_price', number_format($totalPrice, 2, '.', ''));
                                            }
                                        }
                                        self::updateOrderTotals($set, $get);
                                    })
                                    ->native(false)
                                    ->columnSpan(1) ,
                                    // ->disabled($isKitchen),

                                TextInput::make('quantity')
                                    ->label('الكمية')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(1001)
                                    // ->reactive()
                                    ->suffixAction(
                                        Action::make('updateQuantity')
                                            ->icon('heroicon-o-check')
                                            ->action(function ($set, $get, $state) {
                                                $unitPrice = (float)($get('unit_price') ?? 0);
                                                $quantity = (int)$state;
                                                $totalPrice = $quantity * $unitPrice;
                                                $set('total_price', number_format($totalPrice, 2, '.', ''));
                                                // استدعاء دالة تحديث المجاميع إذا كانت موجودة
                                            })
                                    )
                                    ->columnSpan(1)
                                    ->disabled($isKitchen),

                                TextInput::make('unit_price')
                                        ->label('سعر الوحدة')
                                        ->numeric()
                                        ->required()
                                        ->minValue(0)
                                        ->step(0.01)
                                        ->readonly()
                                        ->hidden($isKitchen)
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $quantity = (int)($get('quantity') ?? 1);
                                            $unitPrice = (float)$state;
                                            $totalPrice = $quantity * $unitPrice;
                                            $set('total_price', number_format($totalPrice, 2, '.', ''));
                                            self::updateOrderTotals($set, $get);
                                        }),

                                TextInput::make('total_price')
                                    ->label('السعر الإجمالي')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->readonly()
                                    ->hidden($isKitchen)
                                    ->dehydrated()
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->defaultItems(1)

                            ->addActionLabel('إضافة وجبة جديدة')
                            ->addable(!$isKitchen)
                            ->minItems(1)
                            ->maxItems(50)
                            ->reorderable()
                            ->disableItemDeletion()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                isset($state['meal_id']) && $state['meal_id'] ?
                                Meal::find($state['meal_id'])?->name . ' (×' . ($state['quantity'] ?? 1) . ')' :
                                'وجبة جديدة'
                            )
                            ->grid(1)

                            // ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::updateOrderTotals($set, $get);
                            }) ,

                        Placeholder::make('items_total')
                            ->label('المجموع الكلي للوجبات')
                            ->content(function (callable $get) {
                                $items = $get('orderItems') ?? [];
                                $total = 0;
                                foreach ($items as $item) {
                                    if (isset($item['total_price']) && is_numeric($item['total_price'])) {
                                        $total += (float)$item['total_price'];
                                    }
                                }
                                return number_format($total, 2);
                            })
                            ->extraAttributes(['class' => 'text-lg font-bold text-green-600'])
                            ->visible(!$isKitchen) ,

                    ]),
                Section::make('معلومات العميل')
                    ->schema([
                        TextInput::make('customer_name')
                            ->label('اسم العميل')
                            ->required()
                            ->disabled($isKitchen)
                            ->maxLength(255),

                        TextInput::make('customer_phone')
                            ->label('هاتف العميل')
                            ->required()
                            ->tel()
                            ->disabled($isKitchen)
                            ->maxLength(20),

                        TextInput::make('latitude')
                            ->label('خط العرض')
                            ->required()
                            ->disabled($isKitchen)
                            ->numeric()
                            ->step(0.000001)
                            ->prefix('🌎') ,
                            // ->helperText('مثال: 24.7135517'),

                        TextInput::make('longitude')
                            ->label('خط الطول')
                            ->required()
                            ->disabled($isKitchen)
                            ->numeric()
                            ->step(0.000001)
                            ->prefix('🌎') ,
                            // ->helperText('مثال: 46.6752957'),


                        Placeholder::make('map_preview')
                            ->label('موقع العميل على الخريطة')
                            ->content(function ($record) {
                                // في حالة التعديل أو العرض
                                if ($record && $record->latitude && $record->longitude) {
                                    $lat = $record->latitude;
                                    $lng = $record->longitude;

                                    return view('filament.components.map-preview', compact('lat', 'lng'));
                                }

                                // في حالة الإنشاء الجديد
                                $lat = request()->old('latitude');
                                $lng = request()->old('longitude');
                                if ($lat && $lng) {
                                    return view('filament.components.map-preview', compact('lat', 'lng'));
                                }

                                return '<div class="text-center py-4 text-gray-500">سيظهر موقع العميل هنا بعد إدخال الإحداثيات</div>';
                            })
                            ->hidden(fn ($record) => !$record || (!$record->latitude && !$record->longitude))
                            ->html(),


                    ])->columns(2),



                Section::make('معلومات إضافية')
                    ->schema([
                    Textarea::make('special_instructions')
                            ->label('تعليمات خاصة')
                            ->rows(3)
                            ->disabled($isKitchen)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])->columns(1),

                Section::make('الملخص النهائي')
                    ->visible(!$isKitchen)
                    ->schema([
                        Placeholder::make('final_total')
                            ->label('المجموع النهائي')
                            ->content(function (Get $get) {
                                $items = $get('orderItems') ?? [];
                                $total = 0;
                                foreach ($items as $item) {
                                    if (isset($item['total_price']) && is_numeric($item['total_price'])) {
                                        $total += (float)$item['total_price'];
                                    }
                                }
                                return number_format($total, 2)    ;
                            })
                            ->extraAttributes(['class' => 'text-xl font-bold text-green-600']),


                        Hidden::make('total_amount')
                            ->default(0)
                            ->dehydrated()
                            ->reactive()
                            ->afterStateHydrated(function (callable $set, callable $get) {
                                // عند تحميل البيانات
                                $items = $get('orderItems') ?? [];
                                $total = 0;
                                foreach ($items as $item) {
                                    $total += (float)($item['total_price'] ?? 0);
                                }
                                $set('total', $total);
                            }),
                    ])
                    ->columns(1),
            ]);
    }

    private static function updateOrderTotals(Set $set, Get $get): void
        {
            $items = $get('orderItems') ?? [];
            $total = 0;

            foreach ($items as $item) {
                if (isset($item['total_price']) && is_numeric($item['total_price'])) {
                    $total += (float)$item['total_price'];
                }
            }

            $set('total_amount', $total);
    }
}
