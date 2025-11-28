<?php

namespace App\Filament\Resources\Emergencies\Tables;

use App\Models\Emergency;
use App\Models\Meal;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class EmergenciesTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();
        $isKitchen = Auth::user()->hasRole('kitchen');

        return $table
            ->modifyQueryUsing(function ($query) use ($user) {
                if ($user->hasRole('institution')) {
                    return $query->where('institution_id', $user->officialInstitution->id)
                        ;
                }
                if ($user->hasRole('kitchen')) {
                    return $query->where('kitchen_id', $user->kitchen->id)
                    ->whereIn('status',['pending','confirmed']);
                }
                return $query;
            })
            ->columns([
                TextColumn::make('institution.name')
                    ->label('المؤسسة')
                    ->searchable()
                    ->sortable()
                    ->visible(!$user->hasRole('institution')),

                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->searchable()
                    ->sortable()
                    ->visible(!$user->hasRole('kitchen')),

                TextColumn::make('kitchen.name')
                    ->label('المطبخ')
                    ->searchable()
                    ->sortable()
                    ->visible(!$user->hasRole('kitchen')),

                TextColumn::make('order_date')
                    ->label('موعد الاستلام')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->created_at->diffForHumans()),

                TextColumn::make('persons')
                    ->label('عدد الأشخاص')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state)),

                TextColumn::make('total_amount')
                    ->label('المبلغ الإجمالي')
                    ->sortable()
                    ->visible(!$user->hasRole('kitchen'))
                    ->color('success')
                    ->weight('bold'),

                TextColumn::make('meals_details')
                    ->label('تفاصيل الوجبات')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        $items = $record->items->load('meal');

                        if ($items->isEmpty()) {
                            return 'لم يتم التحديد';
                        }

                        // تجميع الوجبات حسب النوع مع جمع الكميات
                        $groupedItems = $items->groupBy('meal_id')->map(function ($group) {
                            $mealName = $group->first()->meal->name ?? 'غير معروف';
                            $totalQuantity = $group->sum('quantity');
                            return "{$mealName} {$totalQuantity}";
                        });

                        // عرض كل وجبة في سطر منفصل
                        return $groupedItems->implode("\n");
                    })
                    ->formatStateUsing(fn ($state) => nl2br(e($state)))
                    ->html()
                    ->wrap()
                    ->tooltip(function ($record) {
                        $total = $record->items->sum('quantity');
                        return "المجموع الكلي: {$total} وجبة";
                    }),

                TextColumn::make('status')
                    ->label('حالة الطلب')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'confirmed' => 'warning',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'confirmed' => 'مؤكد',
                        'delivered' => 'تم التسليم',
                        'cancelled' => 'ملغي',
                    }),

                TextColumn::make('confirmed_at')
                    ->label('تاريخ التأكيد')
                    ->dateTime('Y-m-d H:i')
                    ->hidden($user->hasRole('institution'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('لم يتم التأكيد بعد'),

                TextColumn::make('delivered_at')
                    ->label('تاريخ التسليم')
                    ->dateTime('Y-m-d H:i')
                    ->visible($user->hasRole('super_admin'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('لم يتم التسليم بعد'),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('institution')
                    ->label('المؤسسة')
                    ->visible($user->hasRole('super_admin'))
                    ->relationship('institution', 'name'),

                SelectFilter::make('branch')
                    ->label('الفرع')
                    ->visible($user->hasRole('super_admin'))
                    ->relationship('branch', 'name') ,

                SelectFilter::make('kitchen')
                    ->label('المطبخ')
                    ->relationship('kitchen', 'name')
                    ->visible($user->hasRole('super_admin')),

                SelectFilter::make('status')
                    ->label('حالة الطلب')
                    // ->hidden($user->hasRole('institution'))
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'confirmed' => 'مؤكد',
                        'delivered' => 'تم التسليم',
                    ]),

                Filter::make('order_date')
                    ->label('موعد الاستلام')
                    // ->hidden($user->hasRole('institution'))
                    ->form([
                        DatePicker::make('order_from')
                            ->label('من تاريخ'),
                        DatePicker::make('order_until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['order_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '>=', $date),
                            )
                            ->when(
                                $data['order_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '<=', $date),
                            );
                    }),

                Filter::make('today_orders')
                    ->hidden($user->hasRole('institution'))
                    ->label('طلبات اليوم')
                    ->query(fn (Builder $query): Builder => $query->whereDate('order_date', today()))
                    ->toggle(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('تعديل')
                        ->icon('heroicon-o-pencil'),

                    DeleteAction::make()
                        ->label('حذف')
                        ->icon('heroicon-o-trash'),

                    Action::make('confirm_emergency_order')
                        ->label('تأكيد الطلب')
                        ->icon('heroicon-o-check-circle')
                        ->hidden(Auth::user()->hasRole('institution'))
                        ->visible(fn ($record) => $record->status === 'pending')
                        ->color('success')
                        ->modalHeading('تأكيد الطلب الطارئ وإضافة الوجبات المطلوبة')
                        ->modalDescription(function ($record) {
                            return " تأكيد الطلب الطارئ ل  {$record->institution->name} - عدد الأشخاص:  {$record->persons}";
                        })
                        ->form([
                            // قسم معلومات الطلب الأساسية
                            Section::make('معلومات الطلب الطارئ')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Placeholder::make('institution_info')
                                                ->label('المؤسسة')
                                                ->content(fn ($record) => $record->institution->name)
                                                ->extraAttributes(['class' => 'font-medium']),

                                            Placeholder::make('order_date_info')
                                                ->label('تاريخ الطلب')
                                                ->content(fn ($record) => $record->order_date->format('d/m/Y'))
                                                ->extraAttributes(['class' => 'font-medium']),

                                            Placeholder::make('persons_info')
                                                ->label('عدد الأشخاص')
                                                ->content(fn ($record) => $record->persons)
                                                ->extraAttributes(['class' => 'font-medium']),

                                            Placeholder::make('special_instructions_info')
                                                ->label('تعليمات خاصة')
                                                ->content(fn ($record) => $record->special_instructions ?: 'لا توجد تعليمات خاصة')
                                                ->extraAttributes(['class' => 'bg-gray-50 p-3 rounded']),
                                        ]),
                                ]),

                            // قسم الوجبات المطلوبة
                            Section::make('الوجبات المطلوبة')
                                ->description('حدد الوجبات المطلوبة وكمياتها للطلب الطارئ')
                                ->schema([
                                    Repeater::make('emergency_items') // تغيير الاسم هنا
                                        ->label('الوجبات المطلوبة')
                                        ->schema([
                                            Select::make('meal_id')
                                                ->label('الوجبة')
                                                ->options(Meal::where('is_available', true)
                                                            ->whereIn('meal_type',['breakfast','dinner'])
                                                            ->pluck('name', 'id'))
                                                ->required()
                                                ->searchable()
                                                ->preload()
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
                                                ->columnSpan(2),

                                            TextInput::make('quantity')
                                                ->label('الكمية')
                                                ->numeric()
                                                ->required()
                                                ->minValue(1)
                                                ->maxValue(1000001)
                                                ->suffixAction(
                                                    Action::make('updateQuantity')
                                                        ->icon('heroicon-o-check')
                                                        ->action(function ($set, $get, $state) {
                                                            $unitPrice = (float)($get('unit_price') ?? 0);
                                                            $quantity = (int)$state;
                                                            $totalPrice = $quantity * $unitPrice;
                                                            $set('total_price', number_format($totalPrice, 2, '.', ''));
                                                            self::updateOrderTotals($set, $get);
                                                        })
                                                )
                                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                    $unitPrice = (float)($get('unit_price') ?? 0);
                                                    $quantity = (int)$state;
                                                    $totalPrice = $quantity * $unitPrice;
                                                    $set('total_price', number_format($totalPrice, 2, '.', ''));
                                                    self::updateOrderTotals($set, $get);
                                                })
                                                ->columnSpan(1),

                                            TextInput::make('unit_price')
                                                ->label('سعر الوحدة')
                                                ->numeric()
                                                ->required()
                                                ->minValue(0)
                                                ->step(0.01)
                                                ->readonly()
                                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
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
                                                ->dehydrated()
                                                ->columnSpan(1),
                                        ])
                                        ->columns(4)
                                        ->columnSpanFull()
                                        ->defaultItems(1)
                                        ->addActionLabel('إضافة وجبة جديدة')
                                        ->addable()
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
                                        ->afterStateUpdated(function (Set $set, Get $get) {
                                            self::updateOrderTotals($set, $get);
                                        }),

                                    Placeholder::make('items_total')
                                        ->label('المجموع الكلي للوجبات')
                                        ->content(function (Get $get) {
                                            $items = $get('emergency_items') ?? [];
                                            $total = 0;
                                            foreach ($items as $item) {
                                                if (isset($item['total_price']) && is_numeric($item['total_price'])) {
                                                    $total += (float)$item['total_price'];
                                                }
                                            }
                                            return number_format($total, 2);
                                        })
                                        ->extraAttributes(['class' => 'text-lg font-bold text-green-600']),
                                ]),

                            Section::make('الملخص النهائي')
                                ->hidden(Auth::user()->hasRole('institution'))
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Placeholder::make('final_total')
                                                ->label('المجموع النهائي')
                                                ->hidden(Auth::user()->hasRole('institution'))
                                                ->content(function (Get $get) {
                                                    $items = $get('emergency_items') ?? [];
                                                    $total = 0;
                                                    foreach ($items as $item) {
                                                        if (isset($item['total_price']) && is_numeric($item['total_price'])) {
                                                            $total += (float)$item['total_price'];
                                                        }
                                                    }
                                                    return number_format($total, 2);
                                                })
                                                ->extraAttributes(['class' => 'text-xl font-bold text-green-600']),
                                        ]),

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
                                ->afterStateHydrated(function (Set $set, Get $get) {
                                    $items = $get('emergency_items') ?? [];
                                    $total = 0;
                                    foreach ($items as $item) {
                                        $total += (float)($item['total_price'] ?? 0);
                                    }
                                    $set('total_amount', $total);
                                }),
                        ])
                        ->action(function ($record, array $data) {
                            try {
                                // 1. حذف العناصر القديمة إذا كانت موجودة
                                $record->items()->delete();

                                // 2. إضافة العناصر الجديدة - استخدام emergency_items بدلاً من items
                                if (isset($data['emergency_items']) && is_array($data['emergency_items'])) {
                                    foreach ($data['emergency_items'] as $itemData) {
                                        $record->items()->create([
                                            'meal_id' => $itemData['meal_id'] ?? null,
                                            'quantity' => $itemData['quantity'] ?? 1,
                                            'unit_price' => $itemData['unit_price'] ?? 0,
                                            'total_price' => $itemData['total_price'] ?? 0,
                                        ]);
                                    }
                                }

                                // 3. تحديث المبلغ الإجمالي وحالة الطلب
                                $record->update([
                                    'total_amount' => $data['total_amount'] ?? 0,
                                    'status' => 'confirmed',
                                    'confirmed_at' => now(),
                                ]);

                            } catch (\Exception $e) {
                                throw new \Exception('حدث خطأ أثناء تأكيد الطلب: ' . $e->getMessage());
                            }
                        })
                        ->modalSubmitActionLabel('تأكيد الطلب الطارئ')
                        ->modalCancelActionLabel('إلغاء')
                        ->after(function () {
                            Notification::make()
                                ->title('تم تأكيد الطلب الطارئ بنجاح')
                                ->success()
                                ->send();
                        }) ,
                        Action::make('mark_delivered')
                            ->label('تسليم الطلب')
                            ->hidden(Auth::user()->hasRole('institution'))
                            ->visible(fn ($record) => $record->status === 'confirmed')
                            ->icon('heroicon-o-truck')
                            ->color('info')
                            ->action(function ($record) {
                                $record->update([
                                    'status' => 'delivered',
                                    'delivered_at' => now()
                                ]);
                            })
                            ->requiresConfirmation()
                            ->modalHeading('تسليم الطلب')
                            ->modalDescription('هل أنت متأكد من تسليم هذا الطلب؟')
                            ->modalSubmitActionLabel('نعم، تم التسليم')
                            ->modalCancelActionLabel('إلغاء')
                            ->after(function () {
                                Notification::make()
                                    ->title('تم تسليم الطلب بنجاح')
                                    ->success()
                                    ->send();
        }),
                ])
                ->label('الإجراءات')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('primary')
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make()
                    //     ->label('حذف المحدد'),
                ]),
            ])
            ->emptyStateHeading('لا توجد طلبات طارئة')
            ->emptyStateDescription('سيظهر هنا الطلبات الطارئة عند إنشائها.')
            ->emptyStateIcon('heroicon-o-exclamation-triangle')
            ->defaultSort('created_at', 'desc');
    }

    private static function updateOrderTotals(Set $set, Get $get): void
    {
        $items = $get('emergency_items') ?? [];
        $total = 0;

        foreach ($items as $item) {
            if (isset($item['total_price']) && is_numeric($item['total_price'])) {
                $total += (float)$item['total_price'];
            }
        }

        $set('total_amount', $total);
    }
}
