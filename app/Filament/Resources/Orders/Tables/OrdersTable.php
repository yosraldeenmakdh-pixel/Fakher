<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Actions\PrintOrderAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('اسم العميل')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('total_quantity')
                    ->label('عدد الوجبات الكلي')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->orderItems->sum('quantity');
                    })
                    ->formatStateUsing(fn ($state) => $state ?? 0),

                TextColumn::make('total')
                    ->label('المجموع')
                    ->sortable()
                    ->color('success')
                    ->weight('bold')
                    ->formatStateUsing(function ($state, $record) {
                        // إذا كان total فارغاً، احسبه من orderItems
                        if (empty($state) || $state == 0) {
                            $calculatedTotal = $record->orderItems->sum(function ($item) {
                                return ($item->quantity ?? 0) * ($item->unit_price ?? 0);
                            });
                            return  number_format($calculatedTotal, 2, '.', ',');
                        }

                        // تأكد من تنسيق الرقم بشكل صحيح
                        if (is_numeric($state)) {
                            return  number_format($state, 2, '.', ',');
                        }

                        return $state;
                    }),

                TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('total_filter')
                    ->label('تصفية حسب السعر')
                    ->form([
                        TextInput::make('min_price')
                            ->label('السعر الأدنى')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('0.00'),
                        TextInput::make('max_price')
                            ->label('السعر الأقصى')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('10000.00'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['min_price'] ?? null,
                                fn (Builder $query, $minPrice): Builder => $query->where('total', '>=', (float) $minPrice)
                            )
                            ->when(
                                $data['max_price'] ?? null,
                                fn (Builder $query, $maxPrice): Builder => $query->where('total', '<=', (float) $maxPrice)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['min_price'] ?? null) {
                            $indicators[] = 'الحد الأدنى: ' . $data['min_price'];
                        }

                        if ($data['max_price'] ?? null) {
                            $indicators[] = 'الحد الأقصى: ' . $data['max_price'];
                        }

                        return $indicators;
                    }),

                Filter::make('created_at')
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
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                })




            ])
            ->recordActions([
                ActionGroup::make([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                PrintOrderAction::make('print')
                        ->label('تحميل')
                        ->icon('heroicon-o-printer')
                        ->color('success'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);


    }
}
