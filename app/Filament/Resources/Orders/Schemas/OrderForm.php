<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Meal;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الطلب الأساسية')
                    ->schema([
                        Select::make('branch_id')
                            ->label('الفرع')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),

                        TextInput::make('name')
                            ->label('اسم العميل')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('special_instructions')
                            ->label('تعليمات خاصة')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('تفاصيل الوجبات')
                    ->schema([
                        Repeater::make('orderItems')
                            ->relationship('orderItems')

                            ->label('الوجبات المطلوبة')
                            ->schema([
                                Select::make('meal_id')
                                    ->label('الوجبة')
                                    ->options(Meal::where('is_available', true)->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            $meal = Meal::find($state);
                                            if ($meal) {
                                                $set('unit_price', $meal->price);
                                                // حساب السعر الإجمالي تلقائياً
                                                $quantity = (int)($get('quantity') ?? 1);
                                                $totalPrice = $quantity * $meal->price;
                                                $set('total_price', number_format($totalPrice, 2, '.', ''));
                                            }
                                        }
                                        self::updateOrderTotals($set, $get);
                                    })
                                    ->native(false)
                                    ->columnSpan(2),


                                TextInput::make('quantity')
                                    ->label('الكمية')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(1001)
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
                                    ->columnSpan(1),

                                TextInput::make('unit_price')
                                    ->label('سعر الوحدة')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->reactive()
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
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->columnSpanFull()
                            ->defaultItems(1)
                            ->addActionLabel('إضافة وجبة جديدة')
                            ->minItems(1)
                            ->maxItems(50)
                            ->reorderable()
                            // ->cloneable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                isset($state['meal_id']) && $state['meal_id'] ?
                                Meal::find($state['meal_id'])?->name . ' (×' . ($state['quantity'] ?? 1) . ')' :
                                'وجبة جديدة'
                            )
                            ->grid(1)
                            ->live()
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                self::updateOrderTotals($set, $get);
                            })
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                return $data;
                            }),

                        Placeholder::make('items_total')
                            ->label('المجموع الكلي للوجبات')
                            ->live() // أضفنا live هنا
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
                            ->extraAttributes(['class' => 'text-lg font-bold text-green-600']),

                        Action::make('updateTotals')
                            ->label('تحديث الأسعار')
                            ->icon('heroicon-o-calculator')
                            ->action(function ($set, $get) {
                                $items = $get('orderItems') ?? [];
                                foreach ($items as $index => $item) {
                                    $quantity = (int)($item['quantity'] ?? 1);
                                    $unitPrice = (float)($item['unit_price'] ?? 0);
                                    $totalPrice = $quantity * $unitPrice;
                                    $set("orderItems.{$index}.total_price", number_format($totalPrice, 2, '.', ''));
                                }
                                // تحديث المجموع الكلي
                                self::updateOrderTotals($set, $get);
                            }),
                    ]),

                Section::make('الملخص النهائي')
                    ->schema([
                        Placeholder::make('final_total')
                            ->label('المجموع النهائي')
                            ->live() // أضفنا live هنا أيضًا
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
                            ->extraAttributes(['class' => 'text-xl font-bold text-green-600']),

                        Hidden::make('total')
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
                    ])->columns(1),
            ]);
    }

    /**
     * دالة مساعدة لتحديث المجاميع
     */
    private static function updateOrderTotals(callable $set, callable $get): void
    {
        $items = $get('orderItems') ?? [];
        $total = 0;

        foreach ($items as $item) {
            if (isset($item['total_price']) && is_numeric($item['total_price'])) {
                $total += (float)$item['total_price'];
            }
        }

        $set('total', number_format($total, 2, '.', ''));
    }
}
