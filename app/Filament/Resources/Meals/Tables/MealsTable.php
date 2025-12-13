<?php

namespace App\Filament\Resources\Meals\Tables;

use App\Models\Meal;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class MealsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('category.name')
                    ->label('الصنف')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('meal_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'breakfast' => 'فطور',
                        'lunch' => 'غداء',
                        'dinner' => 'عشاء',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'breakfast' => 'success',
                        'lunch' => 'warning',
                        'dinner' => 'danger',
                    })
                    ->label('النوع'),

                TextColumn::make('price')
                    ->label('السعر')
                    ->money('usd')
                    ->sortable()
                    ->color('success')
                    ->weight('bold'),

                IconColumn::make('is_available')
                    ->label('متاح')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('تاريخ الانشاء')
                    ->dateTime('M j, Y g:i A')
                    ->date('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // TextColumn::make('updated_at')
                //     ->label('Updated')
                //     ->dateTime('M j, Y g:i A')
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('category')
                    ->label('الصنف')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),


                TernaryFilter::make('is_available')
                    ->label('التوفر')
                    ->placeholder('جميع الوجبات')
                    ->trueLabel('الوجبات المتاحة')
                    ->falseLabel('الوجبات غير المتاحة'),

                SelectFilter::make('meal_type')
                    ->label('نوع الوجبة')
                    ->options([
                        'breakfast' => 'فطور',
                        'lunch' => 'غداء',
                        'dinner' => 'عشاء',
                    ])
                    ->multiple() // اختياري: إذا كنت تريد السماح بتحديد أكثر من نوع
                    ->searchable() ,

                Filter::make('price_range')
                    ->label('حدود السعر')
                    ->form([
                        TextInput::make('min_price')
                            ->label('السعر الأدنى')
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('max_price')
                            ->label('السعر الأعلى')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['min_price'] ?? null),
                                fn (Builder $query): Builder => $query->where('price', '>=', (float) $data['min_price'])
                            )
                            ->when(
                                filled($data['max_price'] ?? null),
                                fn (Builder $query): Builder => $query->where('price', '<=', (float) $data['max_price'])
                            );
                    })

            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->before(function (Meal $record) {
                            if ($record->image) {
                                Storage::disk('public')->delete($record->image);
                            }
                        }),
                ])
                ->label('الإجراءات')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('primary')
                ->button()
                ->size('sm'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
