<?php

namespace App\Filament\Resources\Meals\Tables;

use App\Models\Meal;
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
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('category.name')
                    ->label('Category')
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
                    ->label('نوع الوجبة'),

                TextColumn::make('price')
                    ->label('price')
                    ->money('usd')
                    ->sortable()
                    ->color('success')
                    ->weight('bold'),

                IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('category')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),


                TernaryFilter::make('is_available')
                    ->label('Availability')
                    ->placeholder('All meals')
                    ->trueLabel('Available meals')
                    ->falseLabel('Unavailable meals'),

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
                    ->label('Price Range')
                    ->form([
                        TextInput::make('min_price')
                            ->label('Min Price')
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('max_price')
                            ->label('Max Price')
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
                EditAction::make() ,
                DeleteAction::make()
                        ->before(function (Meal $record) {
                            // Delete image before deleting the record
                            if ($record->image) {
                                Storage::disk('public')->delete($record->image);
                            }
                        }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
