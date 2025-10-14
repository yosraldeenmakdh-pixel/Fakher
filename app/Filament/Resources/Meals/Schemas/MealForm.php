<?php

namespace App\Filament\Resources\Meals\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class MealForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Meal Name')
                    ->required()
                    ->afterStateUpdated(function ($state, $set) {
                        $set('slug', \Illuminate\Support\Str::slug($state));
                    }),

                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),

                Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('price')
                        ->label('Price')
                        ->required()
                        ->numeric()
                        ->prefix('ل.س')
                        ->step(0.01),

                Toggle::make('is_available')
                        ->label('Available for Order')
                        ->default(true)
                        ->onColor('success')
                        ->offColor('danger'),

                FileUpload::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->directory('meals')
                    ->image()
                    ->imageEditor()
                    ->maxSize(20480)
                    ->downloadable()
                    ->openable()
                    ->helperText('Maximum file size: 20MB. Allowed formats: JPG, PNG, GIF, WEBP')
                    ->columnSpanFull()
                    ->afterStateUpdated(function ($state, $set, $get, $record) {
                        if ($record && $record->image && $state && $state != $record->image) {
                            Storage::disk('public')->delete($record->image);
                        }
                    })


            ]);
    }
}
