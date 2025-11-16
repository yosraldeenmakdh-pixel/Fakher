<?php

namespace App\Filament\Resources\DailyKitchenSchedules\Tables;

use App\Filament\Resources\DailyKitchenSchedules\DailyKitchenScheduleResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DailyKitchenSchedulesTable
{

    public static function configure(Table $table): Table
    {
        $user = Auth::user();

        return $table

            ->modifyQueryUsing(function ($query) use ($user) {

                if ($user->hasRole('kitchen')) {
                    return $query->where('kitchen_id',$user->kitchen->id);
                }
                return $query;
            })
            ->columns([
                TextColumn::make('kitchen.name')
                    ->label('المطبخ')
                    ->sortable()
                    ->hidden($user->hasRole('kitchen'))
                    ->searchable(),

                TextColumn::make('schedule_date')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable(),

            TextColumn::make('breakfast_meals')
                ->label('وجبات الفطور')
                ->getStateUsing(fn (Model $record): string => $record->breakfastMeals
                    ->map(fn($meal) => $meal->meal->name)
                    ->join(' , ') ?: 'لا توجد وجبات')
                ->limit(50)
                ->tooltip(function (Model $record): string {
                    $meals = $record->breakfastMeals->map(fn($meal) => $meal->meal->name);
                    return $meals->isNotEmpty() ? $meals->join(' , ') : 'لا توجد وجبات';
                }),

            TextColumn::make('lunch_meals')
                ->label('وجبات الغداء')
                ->getStateUsing(fn (Model $record): string => $record->lunchMeals
                    ->map(fn($meal) => $meal->meal->name)
                    ->join(' , ') ?: 'لا توجد وجبات')
                ->limit(50)
                ->tooltip(function (Model $record): string {
                    $meals = $record->lunchMeals->map(fn($meal) => $meal->meal->name);
                    return $meals->isNotEmpty() ? $meals->join(' , ') : 'لا توجد وجبات';
                }),

            TextColumn::make('dinner_meals')
                ->label('وجبات العشاء')
                ->getStateUsing(fn (Model $record): string => $record->dinnerMeals
                    ->map(fn($meal) => $meal->meal->name)
                    ->join(' , ') ?: 'لا توجد وجبات')
                ->limit(50)
                ->tooltip(function (Model $record): string {
                    $meals = $record->dinnerMeals->map(fn($meal) => $meal->meal->name);
                    return $meals->isNotEmpty() ? $meals->join(' , ') : 'لا توجد وجبات';
                }),



                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                SelectFilter::make('kitchen')
                    ->label('المطبخ')
                    ->relationship('kitchen', 'name')
                    ->searchable()
                    ->hidden($user->hasRole('kitchen'))
                    ->preload(),

                Filter::make('schedule_date')
                    ->label('تاريخ الجدولة')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('من تاريخ'),
                        DatePicker::make('date_until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('schedule_date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('schedule_date', '<=', $date),
                            );
                    }),

            ])
            ->recordActions([
                EditAction::make()
                    ->label('تعديل'),

                Action::make('viewMeals')
                    ->label('عرض الوجبات')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (Model $record): string => DailyKitchenScheduleResource::getUrl('edit', ['record' => $record])),

                DeleteAction::make()
                    ->label('حذف'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
