<?php

namespace App\Filament\Resources\Offers\Tables;

use App\Models\Offer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class OffersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/placeholder.jpg'))
                    ->extraImgAttributes(['class' => 'object-cover']) ,

                TextColumn::make('name')
                    ->label('اسم العرض')
                    ->searchable()
                    ->sortable()
                    ->weight('medium') ,
                    // ->description(fn (Offer $record): string => $record->description ? Str::limit($record->description, 50) : ''),

                TextColumn::make('discount_value')
                    ->label('قيمة الخصم')
                    // ->money('SAR')
                    ->sortable()
                    ->color('success')
                    ->icon('heroicon-o-currency-dollar'),

                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('حالة العرض')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->nullable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
