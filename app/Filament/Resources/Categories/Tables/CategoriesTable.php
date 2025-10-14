<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->width(60)
                    ->height(60)
                    ->square()
                    ->defaultImageUrl(url('/placeholder.jpg')),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium') ,

                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->toggleable()
                    ->limit(30)
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),


                IconColumn::make('has_image')
                    ->label('Has Image')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->image))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                // TextColumn::make('items_count')
                //     ->label('Products')
                //     ->counts('meals') // Assuming you have products relationship
                //     ->sortable()
                //     ->color('primary')
                //     ->icon('heroicon-o-shopping-bag'),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make() ,
                DeleteAction::make()
                    ->before(function (Category $record) {
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
