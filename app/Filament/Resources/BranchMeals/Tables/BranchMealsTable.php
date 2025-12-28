<?php

namespace App\Filament\Resources\BranchMeals\Tables;

use App\Models\Branch;
use App\Models\BranchMeal;
use App\Models\Meal;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BranchMealsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => $record->branch->location ?? ''),

                TextColumn::make('meal.name')
                    ->label('الوجبة')
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => number_format($record->meal->price, 2) . ' $'),

                IconColumn::make('is_available')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->options(Branch::all()->pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('meal_id')
                    ->label('الوجبة')
                    ->options(Meal::all()->pluck('name', 'id'))
                    ->searchable(),

                TernaryFilter::make('is_available')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('متاحة فقط')
                    ->falseLabel('غير متاحة فقط'),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('toggleAvailability')
                        ->label('تغيير الحالة')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(function (BranchMeal $record) {
                            $record->update([
                                'is_available' => !$record->is_available
                            ]);
                        })
                        ->tooltip('تبديل حالة التوفر'),
                    EditAction::make()
                        ->label('تعديل') ,
                    DeleteAction::make()
                        ->label('حذف')
                        ->requiresConfirmation()
                        ->modalHeading('حذف الفرع')
                        ->modalDescription('هل أنت متأكد من رغبتك في حذف هذا الفرع؟ لا يمكن التراجع عن هذا الإجراء.')
                        ->modalSubmitActionLabel('نعم، احذف'),

                ])
                ->label('الإجراءات')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('primary')
                ->button()
                ->size('sm'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
            ]);
    }
}
