<?php

namespace App\Filament\Resources\OrderOnlines\Tables;

use App\Models\OrderOnline;
use DeepCopy\Filter\Filter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter as FiltersFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OrderOnlinesTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->modifyQueryUsing(function ($query) use ($user) {
                $expiredOrders = OrderOnline::where('order_date', '<', now())
                    ->whereNotIn('status', ['collecting','delivered', 'cancelled'])
                    ->update(['status' => 'cancelled']);

                if ($user->hasRole('kitchen')) {
                    return $query
                        ->where('status', 'Pending')
                        ->where('order_date', '>', now())
                        ->where('kitchen_id',$user->kitchen->id);
                }
                return $query;
            })
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('اسم العميل')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_phone')
                    ->label('هاتف العميل')
                    ->searchable(),

                // TextColumn::make('location')
                    // ->label('الموقع')
                    // ->formatStateUsing(function ($record) {
                    //     if ($record->latitude && $record->longitude) {
                    //         return '
                    //             <div class="flex items-center gap-2">
                    //                 <span class="text-red-500">📍</span>
                    //                 <span class="text-sm font-mono">
                    //                     ' . number_format($record->latitude, 4) . ', ' . number_format($record->longitude, 4) . '
                    //                 </span>
                    //             </div>
                    //         ';
                    //     }
                    //     return '<span class="text-gray-400">❌ لا يوجد</span>';
                    // })
                    // ->html()
                    // ->searchable(false)
                    // ->sortable(false),

                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->sortable(),

                TextColumn::make('kitchen.name')
                    ->label('المطبخ')
                    ->searchable()
                    ->visible(!$user->hasRole('kitchen'))
                    ->sortable() ,

                TextColumn::make('total_quantity')
                    ->label('عدد الوجبات الكلي')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->items->sum('quantity');
                    })
                    ->formatStateUsing(fn ($state) => $state ?? 0),

                TextColumn::make('total')
                    ->label('المبلغ الإجمالي')
                    // ->money()
                    ->visible(!$user->hasRole('kitchen'))
                    ->sortable(),

                TextColumn::make('confirmed_at')
                    ->label('وقت التأكيد')
                    ->visible(!$user->hasRole('kitchen'))
                    ->sortable(),
                TextColumn::make('delivered_at')
                    ->label('وقت التوصيل')
                    ->visible(!$user->hasRole('kitchen'))
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'warning' => 'collecting',
                        'primary' => 'pending',
                        'primary' => 'confirmed',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'collecting' => 'جمع الطلب',
                        'pending' => 'قيد الانتظار',
                        'confirmed' => 'مؤكد',
                        'delivered' => 'تم التوصيل',
                        'cancelled' => 'ملغي',
                    }),

                TextColumn::make('order_date')
                    ->label('توقيت التسليم المطلوب')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('حالة الطلب')
                    ->visible(!$user->hasRole('kitchen'))
                    ->options([
                        'collecting' => 'جمع الطلب',
                        'pending' => 'قيد الانتظار',
                        'delivered' => 'تم التوصيل',
                        'cancelled' => 'ملغي',
                    ]),

                SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->visible(!$user->hasRole('kitchen'))
                    ->relationship('branch', 'name'),

                FiltersFilter::make('order_date')
                    ->label('تاريخ الطلب')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('من تاريخ'),
                        DatePicker::make('created_until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('view_map')
                    ->label('عرض الخريطة')
                    ->icon('heroicon-o-map')
                    ->color('success')
                    ->hidden(fn ($record) => !$record->latitude || !$record->longitude)
                    ->action(function ($record) {
                        // يمكن فتح نافذة جديدة أو استخدام modal
                        $url = "https://www.google.com/maps?q={$record->latitude},{$record->longitude}";
                        return redirect()->away($url);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
