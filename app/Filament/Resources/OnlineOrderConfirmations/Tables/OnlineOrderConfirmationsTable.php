<?php

namespace App\Filament\Resources\OnlineOrderConfirmations\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OnlineOrderConfirmationsTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user() ;
        $isKitchen = $user->hasRole('kitchen') ;
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->hasRole('kitchen')) {
                    return $query->where('kitchen_id', Auth::user()->kitchen->id)->where('status', 'confirmed');
                }

                return $query;
            })
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('kitchen.name')
                    ->label('المطيخ')
                    ->searchable()
                    ->hidden($isKitchen)
                    ->sortable()
                    ->weight('bold'),
                    // ->color('primary'),

                TextColumn::make('order.customer_name')
                    ->label('اسم العميل')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),

                TextColumn::make('order.customer_phone')
                    ->label('هاتف العميل')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('delivery_date')
                    ->label('موعد التسليم المطلوب')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->icon('heroicon-o-clock'),

                TextColumn::make('total_amount')
                    ->label('المجموع')
                    ->hidden($isKitchen)
                    ->sortable(),
                    // ->icon('heroicon-o-currency-dollar'),

                BadgeColumn::make('status')
                    ->label('حالة الطلب')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'confirmed',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'confirmed' => 'تم التأكيد',
                        'delivered' => 'تم التسليم',
                        'cancelled' => 'ملغي',
                    }),

                TextColumn::make('order_items')
                    ->label('تفاصيل الوجبات')
                    ->formatStateUsing(function ($state) {
                        // إذا كان $state هو string، قم بتحويله إلى array
                        if (is_string($state)) {
                            $items = json_decode($state, true) ?? [];
                        } else {
                            $items = $state ?? [];
                        }

                        if (empty($items)) {
                            return '---';
                        }

                        return collect($items)->take(2)->map(function ($item) {
                            // تأكد من أن $item هي array وليس string
                            if (is_array($item)) {
                                $mealName = $item['meal_name'] ?? 'وجبة غير معروفة';
                                $quantity = $item['quantity'] ?? 0;
                                return "{$mealName} (×{$quantity})";
                            } else {
                                return 'بيانات غير صالحة';
                            }
                        })->implode(' - ') . (count($items) > 2 ? ' ...+' : '');
                    })
                    ->wrap(),


                TextColumn::make('kitchen.name')
                    ->label('المطبخ')
                    ->searchable()
                    ->hidden($isKitchen)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('special_instructions')
                    ->label('تعليمات خاصة')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('delivered_at')
                    ->label('موعد التسليم الفعلي')
                    ->dateTime()
                    ->hidden($isKitchen)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('تاريخ التأكيد')
                    ->dateTime('Y-m-d H:i')
                    ->hidden($isKitchen)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('حالة الطلب')
                    ->hidden($isKitchen)
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'confirmed' => 'تم التأكيد',
                        'delivered' => 'تم التسليم',
                        'cancelled' => 'ملغي',
                    ]),

                Tables\Filters\SelectFilter::make('kitchen_id')
                    ->label('المطبخ')
                    ->hidden($isKitchen)
                    ->relationship('kitchen', 'name'),

                Tables\Filters\Filter::make('delivery_date')
                    ->label('موعد التسليم')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('delivery_from')
                            ->label('من تاريخ'),
                        \Filament\Forms\Components\DatePicker::make('delivery_until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['delivery_from'],
                                fn($query, $date) => $query->whereDate('delivery_date', '>=', $date)
                            )
                            ->when(
                                $data['delivery_until'],
                                fn($query, $date) => $query->whereDate('delivery_date', '<=', $date)
                            );
                    }),
            ])
            ->actions([

                EditAction::make(),
            ])
            ->bulkActions([

            ]) ;

    }
}
