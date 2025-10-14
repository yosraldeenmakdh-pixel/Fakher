<?php

namespace App\Filament\Resources\Branches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->icon('heroicon-o-phone'),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('opening_time')
                    ->time('H:i')
                    ->icon('heroicon-o-clock')
                    ->color('success')
                    ->sortable(),
                TextColumn::make('closing_time')
                    ->time('H:i')
                    ->icon('heroicon-o-clock')
                    ->color('danger')
                    ->sortable(),

                TextColumn::make('working_hours')
                    ->label('working hours')
                    ->getStateUsing(fn ($record) => $record->opening_time->format('H:i') . ' - ' . $record->closing_time->format('H:i')),

                IconColumn::make('is_open')
                    ->label('status')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        $now = now()->format('H:i:s');
                        return $now >= $record->opening_time->format('H:i:s') &&
                               $now <= $record->closing_time->format('H:i:s');
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),



                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                // Filter::make('working_hours')
                // ->label('Work period')
                // ->form([
                //     TimePicker::make('opening_from')
                //         ->label('opens from'),
                //     TimePicker::make('opening_until')
                //         ->label('Open up'),
                // ])
                // ->query(function (Builder $query, array $data): Builder {
                //     return $query
                //         ->when(
                //             $data['opening_from'],
                //             fn (Builder $query, $date): Builder => $query->where('opening_time', '>=', $date),
                //         )
                //         ->when(
                //             $data['opening_until'],
                //             fn (Builder $query, $date): Builder => $query->where('opening_time', '<=', $date),
                //         );
                // }),


                Filter::make('open_now')
                ->label('open now')
                ->query(fn (Builder $query): Builder => $query->where('opening_time', '<=', now()->format('H:i:s'))
                                                        ->where('closing_time', '>=', now()->format('H:i:s'))),


                // Filter::make('closing_time')
                // ->label('closing time')
                // ->form([
                //     TimePicker::make('closing_after')
                //         ->label('يغلق بعد الساعة'),
                // ])
                // ->query(function (Builder $query, array $data): Builder {
                //     return $query
                //         ->when(
                //             $data['closing_after'],
                //             fn (Builder $query, $time): Builder => $query->where('closing_time', '>=', $time),
                //         );
                // }),


            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
