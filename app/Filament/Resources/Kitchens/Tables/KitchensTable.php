<?php

namespace App\Filament\Resources\Kitchens\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;


class KitchensTable
{
    public static function configure(Table $table): Table
    {

        $user = Auth::user() ;

        return $table

            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->hasRole('kitchen')) {
                    return $query->where('user_id', Auth::id());
                }

                return $query;
            })
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المطبخ')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('المسؤول')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contact_phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->icon('heroicon-o-phone'),

                TextColumn::make('contact_email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(!$user->hasRole('kitchen')) ,
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(!$user->hasRole('kitchen')) ,
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
