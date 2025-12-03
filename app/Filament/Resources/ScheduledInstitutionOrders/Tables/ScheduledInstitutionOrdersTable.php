<?php

namespace App\Filament\Resources\ScheduledInstitutionOrders\Tables;

use App\Models\DailyScheduleMeal;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ScheduledInstitutionOrdersTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();
        $isInstitution = $user->hasRole('institution') ;
        $isKitchen = $user->hasRole('kitchen') ;

        return $table
            ->modifyQueryUsing(function ($query) use ($user) {
                if ($user->hasRole('institution')) {
                    return $query->where('institution_id', $user->officialInstitution->id);
                }
                if ($user->hasRole('kitchen')) {
                    return $query->whereIn('status', ['pending','confirmed'])->where('kitchen_id',$user->kitchen->id);
                }
                return $query;
            })
            ->columns([

               TextColumn::make('institution.name')
                    ->label('المؤسسة')
                    ->sortable()
                    ->searchable()
                    ->hidden($isInstitution),

                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->sortable()
                    ->searchable()
                    ->hidden($isKitchen) ,

                TextColumn::make('kitchen.name')
                    ->label('المطبخ')
                    ->sortable()
                    ->searchable()
                    ->hidden($isKitchen) ,

                TextColumn::make('order_date')
                    ->label('تاريخ الطلب')
                    ->date('d/m/Y')
                    ->sortable(),

                // عمود الفطور - أسماء الوجبات وعددها
                TextColumn::make('breakfast_meals')
    ->label('🍳 الفطور')
    ->getStateUsing(function ($record) {
        $breakfastMeals = $record->orderMeals->filter(function ($orderMeal) {
            return $orderMeal->scheduleMeal->meal_type === 'breakfast';
        });

        if ($breakfastMeals->isEmpty()) {
            return 'لا توجد وجبات';
        }

        $meals = [];
        foreach ($breakfastMeals as $meal) {
            $meals[] = "{$meal->scheduleMeal->meal->name} ({$meal->quantity})";
        }

        return implode('  ،  ', $meals);
    })
    ->limit(10)
    ->tooltip(function ($record) {
        // نفس كود getStateUsing لكن بدون limit
        $breakfastMeals = $record->orderMeals->filter(function ($orderMeal) {
            return $orderMeal->scheduleMeal->meal_type === 'breakfast';
        });

        if ($breakfastMeals->isEmpty()) {
            return 'لا توجد وجبات';
        }

        $meals = [];
        foreach ($breakfastMeals as $meal) {
            $meals[] = "{$meal->scheduleMeal->meal->name} ({$meal->quantity})";
        }

        return implode('  ،  ', $meals);
    })
                    // ->tooltip(function ($record) {
                    //     $breakfastMeals = $record->orderMeals->filter(function ($orderMeal) {
                    //         return $orderMeal->scheduleMeal->meal_type === 'breakfast';
                    //     });

                    //     if ($breakfastMeals->isEmpty()) {
                    //         return 'لا توجد وجبات فطور';
                    //     }

                    //     $output = [];
                    //     $totalQuantity = 0;
                    //     foreach ($breakfastMeals as $meal) {
                    //         $output[] = "🍽️ <strong>{$meal->scheduleMeal->meal->name}</strong>";
                    //         $output[] = "   - الكمية: {$meal->quantity} وجبة";
                    //         $output[] = "   - السعر: {$meal->unit_price}$ للوجبة";
                    //         $output[] = "<div style='height: 5px;'></div>"; // مسافة بين الوجبات
                    //         $totalQuantity += $meal->quantity;
                    //     }
                    //     $output[] = "<hr style='margin: 8px 0;'>";
                    //     $output[] = "📊 <strong>الإجمالي: {$totalQuantity} وجبة</strong>";

                    //     return new HtmlString(implode("<br>", $output));
                    // })
                    ->wrap(),

                TextColumn::make('lunch_meals')
                    ->label('🍽️ الغداء')
                    ->getStateUsing(function ($record) {
                        $lunchMeals = $record->orderMeals->filter(function ($orderMeal) {
                            return $orderMeal->scheduleMeal->meal_type === 'lunch';
                        });

                        if ($lunchMeals->isEmpty()) {
                            return 'لا توجد وجبات';
                        }

                        $meals = [];
                        foreach ($lunchMeals as $meal) {
                            $meals[] = "{$meal->scheduleMeal->meal->name} ({$meal->quantity})";
                        }

                        return implode('  ،  ', $meals);
                    })
                    ->limit(10)
                    // ->tooltip(function ($record) {
                    //     $lunchMeals = $record->orderMeals->filter(function ($orderMeal) {
                    //         return $orderMeal->scheduleMeal->meal_type === 'lunch';
                    //     });

                    //     if ($lunchMeals->isEmpty()) {
                    //         return 'لا توجد وجبات غداء';
                    //     }

                    //     $output = [];
                    //     $totalQuantity = 0;
                    //     foreach ($lunchMeals as $meal) {
                    //         $output[] = "🍽️ <strong>{$meal->scheduleMeal->meal->name}</strong>";
                    //         $output[] = "   - الكمية: {$meal->quantity} وجبة";
                    //         $output[] = "   - السعر: {$meal->unit_price}$ للوجبة";
                    //         $output[] = "<div style='height: 5px;'></div>";
                    //         $totalQuantity += $meal->quantity;
                    //     }
                    //     $output[] = "<hr style='margin: 8px 0;'>";
                    //     $output[] = "📊 <strong>الإجمالي: {$totalQuantity} وجبة</strong>";

                    //     return new HtmlString(implode("<br>", $output));
                    // })
                    ->wrap(),

                TextColumn::make('dinner_meals')
                    ->label('🌙 العشاء')
                    ->getStateUsing(function ($record) {
                        $dinnerMeals = $record->orderMeals->filter(function ($orderMeal) {
                            return $orderMeal->scheduleMeal->meal_type === 'dinner';
                        });

                        if ($dinnerMeals->isEmpty()) {
                            return 'لا توجد وجبات';
                        }

                        $meals = [];
                        foreach ($dinnerMeals as $meal) {
                            $meals[] = "{$meal->scheduleMeal->meal->name} ({$meal->quantity})";
                        }

                        return implode('  ،  ', $meals);
                    })
                    ->limit(10)
                    // ->tooltip(function ($record) {
                    //     $dinnerMeals = $record->orderMeals->filter(function ($orderMeal) {
                    //         return $orderMeal->scheduleMeal->meal_type === 'dinner';
                    //     });

                    //     if ($dinnerMeals->isEmpty()) {
                    //         return 'لا توجد وجبات عشاء';
                    //     }

                    //     $output = [];
                    //     $totalQuantity = 0;
                    //     foreach ($dinnerMeals as $meal) {
                    //         $output[] = "🍽️ <strong>{$meal->scheduleMeal->meal->name}</strong>";
                    //         $output[] = "   - الكمية: {$meal->quantity} وجبة";
                    //         $output[] = "   - السعر: {$meal->unit_price}$ للوجبة";
                    //         $output[] = "<div style='height: 5px;'></div>";
                    //         $totalQuantity += $meal->quantity;
                    //     }
                    //     $output[] = "<hr style='margin: 8px 0;'>";
                    //     $output[] = "📊 <strong>الإجمالي: {$totalQuantity} وجبة</strong>";

                    //     return new HtmlString(implode("<br>", $output));
                    // })
                    ->wrap(),

                // عدد الأشخاص للفطور
                TextColumn::make('breakfast_persons')
                    ->label('أشخاص الفطور')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => $state ?: '0'),

                // عدد الأشخاص للغداء
                TextColumn::make('lunch_persons')
                    ->label('أشخاص الغداء')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state) => $state ?: '0'),

                // عدد الأشخاص للعشاء
                TextColumn::make('dinner_persons')
                    ->label('أشخاص العشاء')
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => $state ?: '0'),

                // إجمالي الأشخاص
                // TextColumn::make('total_persons')
                //     ->label('إجمالي الأشخاص')
                //     ->getStateUsing(fn ($record) => $record->breakfast_persons + $record->lunch_persons + $record->dinner_persons)
                //     ->sortable()
                //     ->badge()
                //     ->color('primary')
                //     ->weight('bold'),

                // السعر الإجمالي
                TextColumn::make('total_amount')
                    ->label('المبلغ الإجمالي')
                    ->money('USD')
                    ->sortable()
                    ->color('success')
                    ->weight('bold'),

                // التعليمات الخاصة
                IconColumn::make('has_special_instructions')
                    ->label('تعليمات خاصة')
                    ->getStateUsing(fn ($record) => !empty($record->special_instructions))
                    ->boolean()
                    ->trueIcon('heroicon-o-chat-bubble-left-ellipsis')
                    ->falseIcon('heroicon-o-chat-bubble-left')
                    ->trueColor('info')
                    ->falseColor('gray') ,
                    // ->trueTooltip('يوجد تعليمات خاصة - ' . fn($record) => $record->special_instructions)
                    // ->falseTooltip('لا توجد تعليمات خاصة'),

                // حالة الطلب
                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'confirmed' => 'مؤكد',
                        'delivered' => 'تم التسليم',
                        'cancelled' => 'ملغي',
                    })
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'confirmed',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'confirmed',
                        'heroicon-o-truck' => 'delivered',
                        'heroicon-o-x-circle' => 'cancelled',
                    ]),

                TextColumn::make('confirmed_at')
                    ->label('تاريخ التأكيد')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true) ,
                    // ->visible(fn () => !$isInstitution),

                TextColumn::make('delivered_at')
                    ->label('تاريخ التسليم')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true) ,
                    // ->visible(fn () => !$isInstitution),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([

                SelectFilter::make('status')
                    ->label('حالة الطلب')
                    ->options(function () use ($user) {
                        $baseOptions = [
                            'pending' => 'قيد الانتظار',
                            'confirmed' => 'مؤكد',
                        ];

                        // إذا كان المستخدم kitchen، نعرض فقط pending و confirmed
                        if ($user->hasRole('kitchen')) {
                            return $baseOptions;
                        }

                        // إذا لم يكن kitchen، نعرض جميع الخيارات
                        return array_merge($baseOptions, [
                            'delivered' => 'تم التسليم',
                            // 'cancelled' => 'ملغي',
                        ]);
                    })
                    ->multiple(),

                // SelectFilter::make('status')
                //     ->label('حالة الطلب')

                //     ->options([
                //         'pending' => 'قيد الانتظار',
                //         'confirmed' => 'مؤكد',
                //         'delivered' => 'تم التسليم',
                //         // 'cancelled' => 'ملغي',
                //     ])
                //     ->multiple(),

                // DateRangeFilter::make('order_date')
                //     ->label('تاريخ الطلب'),

                // DateRangeFilter::make('created_at')
                //     ->label('تاريخ الإنشاء'),

                Filter::make('has_special_instructions')
                    ->label('يحتوي على تعليمات خاصة')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('special_instructions')->where('special_instructions', '!=', '')),

                Filter::make('future_orders')
                    ->label('الطلبات المستقبلية')
                    ->hidden($isKitchen)
                    ->query(fn (Builder $query): Builder => $query->whereDate('order_date', '>=', now())),

                Filter::make('past_orders')
                    ->label('الطلبات المنتهية')
                    ->hidden($isKitchen)
                    ->query(fn (Builder $query): Builder => $query->whereDate('order_date', '<', now())),

                // فلتر حسب المؤسسة (للمشرفين والمطابخ فقط)
                SelectFilter::make('institution_id')
                    ->label('المؤسسة')
                    ->relationship('institution', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => !$isInstitution),

                // فلتر حسب المطبخ (للمؤسسات والمشرفين فقط)
                SelectFilter::make('kitchen_id')
                    ->label('المطبخ')
                    ->relationship('kitchen', 'name')
                    ->searchable()
                    ->preload()
                    ->hidden($isKitchen || $isInstitution),

                // فلتر حسب نوع الوجبة
                // SelectFilter::make('has_breakfast')
                //     ->label('يحتوي على فطور')
                //     ->query(fn (Builder $query): Builder => $query->whereHas('orderMeals.scheduleMeal', function ($q) {
                //         $q->where('meal_type', 'breakfast');
                //     })),

                // SelectFilter::make('has_lunch')
                //     ->label('يحتوي على غداء')
                //     ->query(fn (Builder $query): Builder => $query->whereHas('orderMeals.scheduleMeal', function ($q) {
                //         $q->where('meal_type', 'lunch');
                //     })),

                // SelectFilter::make('has_dinner')
                //     ->label('يحتوي على عشاء')
                //     ->query(fn (Builder $query): Builder => $query->whereHas('orderMeals.scheduleMeal', function ($q) {
                //         $q->where('meal_type', 'dinner');
                //     })),

            ])
            ->recordActions([
                ActionGroup::make([
                    // Action::make('view_full_details')
                    //     ->label('عرض التفاصيل الكاملة')
                    //     ->icon('heroicon-o-eye')
                    //     ->color('primary')
                    //     ->modalHeading('تفاصيل الطلب الكاملة')
                    //     ->modalContent(fn ($record) => new HtmlString(self::getFullOrderDetails($record)))
                    //     ->modalCancelActionLabel('إغلاق'),

                    EditAction::make()
                        ->label('تعديل'),
                        // ->hidden($isKitchen) ,
                        // ->visible(fn ($record) =>
                        //     $record->status == 'pending'
                        // ),


                    Action::make('confirm_order')
                    ->label('تأكيد الطلب وإضافة الوجبات')
                    ->icon('heroicon-o-check-circle')
                    ->hidden($user->hasRole('institution'))
                    ->visible((fn ($record) => $record->status === 'pending'))
                    ->color('success')
                    ->modalHeading('تأكيد الطلب وإضافة الوجبات المطلوبة')
                    ->modalDescription(function ($record) {
                        return "تأكيد طلب {$record->institution->name} بتاريخ {$record->order_date->format('d/m/Y')}";
                    })
                    ->form([
                        // قسم الوجبات المجدولة المتاحة

                         Section::make('عدد الأشخاص المطلوب إطعامهم')
                            ->schema([
                                Placeholder::make('breakfast_persons_info')
                                    ->label('عدد الأشخاص لوجبة الإفطار')
                                    ->content(function ($record) {
                                        return $record->breakfast_persons . ' شخص';
                                    })
                                    ->extraAttributes(['class' => 'font-medium']),

                                Placeholder::make('lunch_persons_info')
                                    ->label('عدد الأشخاص لوجبة الغداء')
                                    ->content(function ($record) {
                                        return $record->lunch_persons . ' شخص';
                                    })
                                    ->extraAttributes(['class' => 'font-medium']),

                                Placeholder::make('dinner_persons_info')
                                    ->label('عدد الأشخاص لوجبة العشاء')
                                    ->content(function ($record) {
                                        return $record->dinner_persons . ' شخص';
                                    })
                                    ->extraAttributes(['class' => 'font-medium']),
                            ])
                            ->columns(3),

                        Section::make('الوجبات المجدولة المتاحة')
                            ->schema([
                                Placeholder::make('available_meals_info')
                                    ->label('الوجبات المتاحة للتاريخ المحدد')
                                    ->content(function ($record) {
                                        $kitchenId = $record->kitchen_id;
                                        $orderDate = $record->order_date;

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
                            ]),



                        // قسم الوجبات المطلوبة (نفس الموجود في الفورم)
                        Section::make('الوجبات المطلوبة')
                            ->description('حدد الوجبات المطلوبة وكمياتها')
                            ->schema([
                                Repeater::make('orderMeals')
                                    ->label('')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('daily_schedule_meal_id')
                                                    ->label('الوجبة')
                                                    ->options(function ($record) {
                                                        $kitchenId = $record->kitchen_id;
                                                        $orderDate = $record->order_date;

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
                                                    // ->default(1)
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
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $total = 0;
                                        foreach ($state as $meal) {
                                            $quantity = $meal['quantity'] ?? 0;
                                            $unitPrice = $meal['unit_price'] ?? 0;
                                            $total += floatval($quantity) * floatval($unitPrice);
                                        }
                                        $set('total_amount', $total);
                                    }),
                            ]),

                        // المبلغ الإجمالي
                        Section::make('المعلومات المالية')
                            ->schema([
                                TextInput::make('total_amount')
                                    ->label('المبلغ الإجمالي النهائي')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ])
                    ->action(function ($record, array $data) {
                        // 1. حذف الوجبات القديمة إذا كانت موجودة
                        $record->orderMeals()->delete();

                        // 2. إضافة الوجبات الجديدة
                        foreach ($data['orderMeals'] as $mealData) {
                            $record->orderMeals()->create($mealData);
                        }

                        // 3. تحديث المبلغ الإجمالي وحالة الطلب
                        $record->update([
                            'total_amount' => $data['total_amount'],
                            'status' => 'confirmed',
                            'confirmed_at' => now(),
                        ]);
                    })
                    ->modalSubmitActionLabel('تأكيد الطلب')
                    ->modalCancelActionLabel('إلغاء')
                    ->after(function () {
                        Notification::make()
                            ->title('تم تأكيد الطلب بنجاح')
                            ->success()
                            ->send();
                    }),


                    Action::make('mark_delivered')
                        ->label('تسليم الطلب')
                        ->hidden($user->hasRole('institution'))
                        ->visible((fn ($record) => $record->status === 'confirmed'))
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->action(function ($record) {
                            $record->update(['status' => 'delivered', 'delivered_at' => now()]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('تسليم الطلب')
                        ->modalDescription('هل أنت متأكد من تسليم هذا الطلب؟')
                        ->modalSubmitActionLabel('نعم، تم التسليم') ,
                        // ->visible(fn ($record) =>
                        //     $record->status === 'confirmed' &&
                        //     Auth::user()->hasRole('kitchen')
                        // ),

                    // Action::make('cancel_order')
                    //     ->label('إلغاء الطلب')
                    //     ->icon('heroicon-o-x-circle')
                    //     ->color('danger')
                    //     ->action(function ($record) {
                    //         $record->update(['status' => 'cancelled']);
                    //     })
                    //     ->requiresConfirmation()
                    //     ->modalHeading('إلغاء الطلب')
                    //     ->modalDescription('هل أنت متأكد من إلغاء هذا الطلب؟')
                    //     ->modalSubmitActionLabel('نعم، قم بالإلغاء') ,
                    //     // ->visible(fn ($record) =>
                    //     //     in_array($record->status, ['pending', 'confirmed']) &&
                    //     //     (Auth::user()->hasRole('institution') || Auth::user()->hasRole('kitchen'))
                    //     // ),

                    DeleteAction::make()
                        ->label('حذف') ,
                        // ->visible(fn ($record) =>
                        //     $record->status === 'pending' &&
                        //     Auth::user()->can('delete', $record)
                        // ),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }


// private static function getFullOrderDetails($record)
// {
//     $html = "<div class='space-y-4'>";

//     $html .= "<h2 class='text-lg font-bold text-center'>تفاصيل الطلب #{$record->id}</h2>";

//     $html .= "<div class='grid grid-cols-2 gap-2'>";
//     $html .= "<div><strong>المؤسسة:</strong> {$record->institution->name}</div>";
//     $html .= "<div><strong>الفرع:</strong> {$record->branch->name}</div>";
//     $html .= "<div><strong>المطبخ:</strong> {$record->kitchen->name}</div>";
//     $html .= "<div><strong>تاريخ الطلب:</strong> {$record->order_date->format('d/m/Y')}</div>";
//     $html .= "</div>";

//     $html .= "<div class='bg-gray-50 p-3 rounded'>";
//     $html .= "<h3 class='font-bold mb-2'>عدد الأشخاص</h3>";
//     $html .= "<div class='grid grid-cols-2 gap-2'>";
//     $html .= "<div>🍳 الفطور: {$record->breakfast_persons} شخص</div>";
//     $html .= "<div>🍽️ الغداء: {$record->lunch_persons} شخص</div>";
//     $html .= "<div>🌙 العشاء: {$record->dinner_persons} شخص</div>";
//     $html .= "<div><strong>الإجمالي:</strong> " . ($record->breakfast_persons + $record->lunch_persons + $record->dinner_persons) . " شخص</div>";
//     $html .= "</div>";
//     $html .= "</div>";

//     $html .= "<div>";
//     $html .= "<h3 class='font-bold mb-3'>الوجبات المطلوبة</h3>";

//     // الفطور
//     $breakfastMeals = $record->orderMeals->filter(fn($m) => $m->scheduleMeal->meal_type === 'breakfast');
//     if ($breakfastMeals->isNotEmpty()) {
//         $html .= "<div class='mb-4'>";
//         $html .= "<h4 class='font-semibold text-amber-600 mb-2'>🍳 الفطور</h4>";
//         foreach ($breakfastMeals as $meal) {
//             $total = $meal->quantity * $meal->unit_price;
//             $html .= "<div class='flex justify-between py-1 border-b'>";
//             $html .= "<span>{$meal->scheduleMeal->meal->name}</span>";
//             $html .= "<span>{$meal->quantity} وجبة × {$meal->unit_price}$ = <strong>{$total}$</strong></span>";
//             $html .= "</div>";
//         }
//         $html .= "</div>";
//     }

//     // الغداء
//     $lunchMeals = $record->orderMeals->filter(fn($m) => $m->scheduleMeal->meal_type === 'lunch');
//     if ($lunchMeals->isNotEmpty()) {
//         $html .= "<div class='mb-4'>";
//         $html .= "<h4 class='font-semibold text-green-600 mb-2'>🍽️ الغداء</h4>";
//         foreach ($lunchMeals as $meal) {
//             $total = $meal->quantity * $meal->unit_price;
//             $html .= "<div class='flex justify-between py-1 border-b'>";
//             $html .= "<span>{$meal->scheduleMeal->meal->name}</span>";
//             $html .= "<span>{$meal->quantity} وجبة × {$meal->unit_price}$ = <strong>{$total}$</strong></span>";
//             $html .= "</div>";
//         }
//         $html .= "</div>";
//     }

//     // العشاء
//     $dinnerMeals = $record->orderMeals->filter(fn($m) => $m->scheduleMeal->meal_type === 'dinner');
//     if ($dinnerMeals->isNotEmpty()) {
//         $html .= "<div class='mb-4'>";
//         $html .= "<h4 class='font-semibold text-blue-600 mb-2'>🌙 العشاء</h4>";
//         foreach ($dinnerMeals as $meal) {
//             $total = $meal->quantity * $meal->unit_price;
//             $html .= "<div class='flex justify-between py-1 border-b'>";
//             $html .= "<span>{$meal->scheduleMeal->meal->name}</span>";
//             $html .= "<span>{$meal->quantity} وجبة × {$meal->unit_price}$ = <strong>{$total}$</strong></span>";
//             $html .= "</div>";
//         }
//         $html .= "</div>";
//     }

//     $html .= "</div>";

//     $html .= "<div class='bg-gray-100 p-3 rounded mt-4'>";
//     $html .= "<div class='flex justify-between items-center font-bold'>";
//     $html .= "<span>المبلغ الإجمالي:</span>";
//     $html .= "<span>{$record->total_amount}$</span>";
//     $html .= "</div>";
//     $html .= "<div class='flex justify-between items-center mt-2'>";
//     $html .= "<span>حالة الطلب:</span>";

//     // تحديد لون الحالة بشكل منفصل
//     $statusClass = 'bg-gray-100 text-gray-800';
//     $statusText = $record->status_name;

//     switch ($record->status) {
//         case 'delivered':
//             $statusClass = 'bg-green-100 text-green-800';
//             break;
//         case 'cancelled':
//             $statusClass = 'bg-red-100 text-red-800';
//             break;
//         case 'confirmed':
//             $statusClass = 'bg-blue-100 text-blue-800';
//             break;
//         case 'pending':
//             $statusClass = 'bg-yellow-100 text-yellow-800';
//             break;
//     }

//     $html .= "<span class='px-2 py-1 rounded text-sm {$statusClass}'>{$statusText}</span>";
//     $html .= "</div>";
//     $html .= "</div>";

//     if ($record->special_instructions) {
//         $html .= "<div class='mt-4'>";
//         $html .= "<h4 class='font-semibold mb-2'>التعليمات الخاصة:</h4>";
//         $html .= "<p class='bg-yellow-50 p-2 rounded'>{$record->special_instructions}</p>";
//         $html .= "</div>";
//     }

//     if ($record->confirmed_at) {
//         $html .= "<div class='text-sm text-gray-600 mt-2'>تاريخ التأكيد: {$record->confirmed_at->format('d/m/Y H:i')}</div>";
//     }

//     if ($record->delivered_at) {
//         $html .= "<div class='text-sm text-gray-600 mt-2'>تاريخ التسليم: {$record->delivered_at->format('d/m/Y H:i')}</div>";
//     }

//     $html .= "</div>";

//     return $html;
// }

}
