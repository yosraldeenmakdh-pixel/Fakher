<?php

namespace App\Filament\Resources\InstitutionOrderConfirmations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InstitutionOrderConfirmationsTable
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
                    ->label('📄 رقم الطلب') // إضافة أيقونة يدوياً
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->color('primary')
                    ->weight('font-bold'),

                TextColumn::make('kitchen.name')
                    ->label('🏪 المطبخ') // إضافة أيقونة يدوياً
                    ->searchable()
                    ->sortable()
                    ->color('success')
                    ->visible(!$user->hasRole('kitchen')) ,

                TextColumn::make('delivery_date')
                    ->label('📅 تاريخ التسليم') // إضافة أيقونة يدوياً
                    ->date('d/m/Y')
                    ->sortable()
                    ->color('gray'),

                TextColumn::make('delivery_time')
                    ->label('🕒 وقت التسليم') // إضافة أيقونة يدوياً
                    ->time('h:i A')
                    ->sortable()
                    ->color('gray'),

                TextColumn::make('total_amount')
                    ->label('💰 المبلغ') // إضافة أيقونة يدوياً
                    // ->money('KWD')
                    ->sortable()
                    ->color('success')
                    ->weight('font-bold')
                    ->visible(!$isKitchen) ,

                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Pending' => '⏳ معلق',
                        'confirmed' => '✅ مؤكد',
                        'delivered' => '📦 تم التسليم',
                        'cancelled' => '❌ ملغي',
                    })
                    ->colors([
                        'warning' => 'Pending',
                        'success' => 'confirmed',
                        'primary' => 'delivered',
                        'danger' => 'cancelled',
                    ]),

                TextColumn::make('delivered_at')
                    ->label('✅ تم التسليم في') // إضافة أيقونة يدوياً
                    ->dateTime('d/m/Y h:i A')
                    ->sortable()
                    ->color('primary')
                    ->placeholder('لم يتم التسليم بعد'),

                TextColumn::make('created_at')
                    ->label('🕒 تاريخ التأكيد') // إضافة أيقونة يدوياً
                    ->dateTime('d/m/Y h:i A')
                    ->sortable()
                    ->color('gray'),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('d/m/Y h:i A')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(!$user->hasRole('kitchen')) ,

            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('حالة الطلب')
                    ->options([
                        'Pending' => '⏳ معلق',
                        'confirmed' => '✅ مؤكد',
                        'delivered' => '📦 تم التسليم',
                        'cancelled' => '❌ ملغي',
                    ])
                    ->visible(!$user->hasRole('kitchen')) ,

                SelectFilter::make('kitchen_id')
                    ->label('المطبخ')
                    ->relationship('kitchen', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(!$user->hasRole('kitchen')) ,

                Filter::make('delivery_date')
                    ->form([
                        DatePicker::make('delivery_from')
                            ->label('الطلبات المؤكدة من تاريخ'),
                        DatePicker::make('delivery_until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['delivery_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('delivery_date', '>=', $date),
                            )
                            ->when(
                                $data['delivery_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('delivery_date', '<=', $date),
                            );
                    }),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('من تاريخ الإنشاء'),
                        DatePicker::make('created_until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
