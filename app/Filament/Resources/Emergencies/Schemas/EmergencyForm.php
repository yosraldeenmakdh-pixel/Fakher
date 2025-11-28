<?php

namespace App\Filament\Resources\Emergencies\Schemas;

use App\Models\Meal;
use DateTime;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class EmergencyForm
{
    public static function configure(Schema $schema): Schema
    {
        $currentInstitution = Auth::user()->officialInstitution;
        $isKitchen = Auth::user()->hasRole('kitchen');

        return $schema
            ->components([
                Section::make('معلومات الطلب الطارئ الأساسية')
                    ->schema([
                        Grid::make(2)
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
                                ]),

                                ...(Auth::user()->hasRole('institution') ? [
                                    Hidden::make('branch_id')
                                        ->default($currentInstitution->branch->id),

                                    Placeholder::make('current_branch')
                                        ->label('الفرع')
                                        ->content($currentInstitution->branch->name ?? 'غير معين')
                                        ->extraAttributes(['class' => 'font-bold']),
                                ] : [
                                    Select::make('branch_id')
                                        ->label('الفرع')
                                        ->relationship('branch', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->disabled($isKitchen),
                                ]),

                                ...(Auth::user()->hasRole('institution') ? [
                                    Hidden::make('kitchen_id')
                                        ->default($currentInstitution->kitchen->id),

                                    Placeholder::make('current_kitchen')
                                        ->label('المطبخ')
                                        ->content($currentInstitution->kitchen->name ?? 'غير معين')
                                        ->extraAttributes(['class' => 'font-bold']),
                                ] : [
                                    Select::make('kitchen_id')
                                        ->label('المطبخ')
                                        ->relationship('kitchen', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->disabled($isKitchen),
                                ]),
                            ]),

                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('order_date')
                                    ->label('تاريخ ووقت الطلب')
                                    ->required()
                                    ->native(false)
                                    ->default(now()->addHours(2)->addMinute(30)) // قيمة افتراضية بعد ساعتين
                                    ->minDate(now()->addHours(2)) // منع اختيار التواريخ قبل ساعتين من الآن
                                    ->disabled($isKitchen)
                                    ->rules([
                                        'required',
                                        'date',
                                        'after_or_equal:' . now()->addHours(2)->format('Y-m-d H:i:s'),
                                    ])
                                    ->helperText('يجب أن يكون تاريخ ووقت الطلب بعد ساعتين على الأقل من الآن (' . now()->addHours(2)->format('Y-m-d H:i') . ')')
                                    ->validationMessages([
                                        'after_or_equal' => 'يجب أن يكون تاريخ ووقت الطلب بعد ساعتين على الأقل من الوقت الحالي.',
                                    ])
                                    ->seconds(false) // إخفاء الثواني إذا لم تكن ضرورية
                                    ->displayFormat('d/m/Y H:i'), // تنسيق العرض

                                TextInput::make('persons')
                                    ->label('عدد الأشخاص')
                                    ->required()
                                    ->numeric()
                                    ->disabled($isKitchen)
                                    // ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        self::updateOrderTotals($set, $get);
                                    }),
                            ]),

                        Grid::make(2)
                            ->schema([
                                ...(Auth::user()->hasRole('institution') ? [
                                    Hidden::make('status')
                                        ->default('pending'),

                                    // Placeholder::make('status_display')
                                    //     ->label('حالة الطلب')
                                    //     ->content('قيد الانتظار')
                                    //     ->extraAttributes(['class' => 'font-bold text-green-600']),
                                ] : [
                                    Select::make('status')
                                        ->label('حالة الطلب')
                                        ->required()
                                        ->options(function ($get, $set) {
                                            $currentStatus = $get('status') ?? 'pending';

                                            $options = [
                                                'pending' => 'قيد الانتظار',
                                                'confirmed' => 'مؤكد',
                                                'delivered' => 'تم التسليم',
                                                'cancelled' => 'ملغي',
                                            ];

                                            if (Auth::user()->hasRole('kitchen')) {
                                                if ($currentStatus === 'pending') {
                                                    // من pending يمكن الانتقال إلى confirmed أو cancelled فقط
                                                    unset($options['delivered']);
                                                    unset($options['pending']);
                                                    unset($options['cancelled']);
                                                } elseif ($currentStatus === 'confirmed') {
                                                    // من confirmed يمكن الانتقال إلى delivered أو cancelled فقط
                                                    unset($options['pending']);
                                                } elseif (in_array($currentStatus, ['delivered', 'cancelled'])) {
                                                    // لا يمكن تغيير الحالة إذا كانت delivered أو cancelled
                                                    return [];
                                                }
                                            }

                                            return $options;
                                        })
                                        ->default('pending')
                                        ->native(false)
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if ($state === 'confirmed') {
                                                $set('confirmed_at', now());
                                            } elseif ($state === 'delivered') {
                                                $set('delivered_at', now());
                                            }
                                        }),
                                ]),

                                Placeholder::make('total_amount_display')
                                    ->label('المبلغ الإجمالي')
                                    ->hidden(Auth::user()->hasRole('institution'))
                                    ->content(function (Get $get) {
                                        $total = $get('total_amount') ?? 0;
                                        return number_format($total, 2) . ' ر.س';
                                    })
                                    ->extraAttributes(['class' => 'text-lg font-bold text-green-600']),
                            ]),
                    ]),

                Section::make('تفاصيل الوجبات الطارئة')
                    ->hidden(Auth::user()->hasRole('institution'))
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->label('الوجبات المطلوبة')
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
                                    ->columnSpan(2)
                                    ->disabled($isKitchen),

                                TextInput::make('quantity')
                                    ->label('الكمية')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(1000001)
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
                            ->columns(4)
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
                                $items = $get('items') ?? [];
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

                Section::make('الملخص النهائي')
                    ->hidden(Auth::user()->hasRole('institution'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('persons_summary')
                                    ->label('عدد الأشخاص')
                                    ->hidden(Auth::user()->hasRole('institution'))
                                    ->content(fn (Get $get) => $get('persons') ?? 0)
                                    ->extraAttributes(['class' => 'font-bold']),

                                Placeholder::make('final_total')
                                    ->label('المجموع النهائي')
                                    ->hidden(Auth::user()->hasRole('institution'))
                                    ->content(function (Get $get) {
                                        $items = $get('items') ?? [];
                                        $total = 0;
                                        foreach ($items as $item) {
                                            if (isset($item['total_price']) && is_numeric($item['total_price'])) {
                                                $total += (float)$item['total_price'];
                                            }
                                        }
                                        return number_format($total, 2) . '$';
                                    })
                                    ->extraAttributes(['class' => 'text-xl font-bold text-green-600']),
                            ]),



                        // الحقول الزمنية المخفية
                        Hidden::make('confirmed_at')
                            ->default(null)
                            ->dehydrated(),

                        Hidden::make('delivered_at')
                            ->default(null)
                            ->dehydrated(),
                    ]),


                        Hidden::make('total_amount')
                            ->default(0)
                            ->dehydrated()
                            ->reactive()
                            ->afterStateHydrated(function (callable $set, callable $get) {
                                // عند تحميل البيانات
                                $items = $get('items') ?? [];
                                $total = 0;
                                foreach ($items as $item) {
                                    $total += (float)($item['total_price'] ?? 0);
                                }
                                $set('total', $total);
                            }),

                Section::make('تعليمات خاصة')
                    ->schema([
                        Textarea::make('special_instructions')
                            ->label('تعليمات خاصة')
                            ->nullable()
                            ->columnSpanFull()
                            ->rows(3)
                            ->disabled($isKitchen)
                            ->placeholder('أي تعليمات إضافية للطلب الطارئ...'),
                    ]),
            ]);
    }

    private static function updateOrderTotals(Set $set, Get $get): void
        {
            $items = $get('items') ?? [];
            $total = 0;

            foreach ($items as $item) {
                if (isset($item['total_price']) && is_numeric($item['total_price'])) {
                    $total += (float)$item['total_price'];
                }
            }

            $set('total_amount', $total);
        }
}
