<?php

namespace App\Filament\Resources\InstitutionalMealPrices\Tables;

use App\Models\InstitutionalMealPrice;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InstitutionalMealPricesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                 TextColumn::make('meal.name')
                    ->label('اسم الوجبة')
                    ->searchable()
                    ->sortable(),
                    // ->description(fn (InstitutionalMealPrice $record): string => $record->meal->description ?? ''),

                TextColumn::make('meal.price')
                    ->label('السعر الأساسي')
                    // ->money('USD')
                    ->sortable()
                    ->color('gray') ,
                    // ->description('السعر العام'),

                TextColumn::make('scheduled_price')
                    ->label('السعر المؤسسي')
                    // ->money('USD')
                    ->sortable()
                    ->color('success') ,
                    // ->description('السعر المؤسسي'),

                TextColumn::make('discount_percentage')
                    ->label('نسبة الخصم')
                    ->getStateUsing(function (InstitutionalMealPrice $record): string {
                        $basePrice = $record->meal->price;
                        if ($basePrice > 0) {
                            $discount = (($basePrice - $record->scheduled_price) / $basePrice) * 100;
                            return number_format($discount, 1) . '%';
                        }
                        return '0%';
                    })
                    ->color('danger')
                    ->sortable(),

                // IconColumn::make('is_active')
                //     ->label('الحالة')
                //     ->boolean()
                //     ->trueIcon('heroicon-o-check-badge')
                //     ->falseIcon('heroicon-o-x-circle')
                //     ->trueColor('success')
                //     ->falseColor('danger')
                //     ->trueTooltip('مفعل')
                //     ->falseTooltip('معطل'),

                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn ($record): string => $record->is_active ? 'مفعل - السعر الحالي' : 'غير مفعل - سعر سابق'),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

            ])
            ->filters([

                SelectFilter::make('meal')
                    ->label('الوجبة')
                    ->relationship('meal', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('المفعلة فقط')
                    ->falseLabel('المعطلة فقط')
                    ->native(false),

                // Filter::make('has_discount')
                //     ->label('الأسعار المخفضة')
                //     ->query(fn (Builder $query): Builder => $query->whereColumn('scheduled_price', '<', 'meal.price'))
                //     ->toggle(),

            ])
            ->recordActions([
                EditAction::make()
                    ->label('تعديل')
                    ->color('primary'),

                Action::make('toggleStatus')
                    ->label('تبديل الحالة')
                    ->icon('heroicon-o-power')
                    ->color('warning')
                    ->action(function (InstitutionalMealPrice $record) {
                        $record->update(['is_active' => !$record->is_active]);
                    }),

                DeleteAction::make()
                    ->label('حذف')
                    ->color('danger'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
