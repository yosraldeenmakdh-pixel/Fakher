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
                    ->label('اسم الوجبة')
                    ->required()
                    ->afterStateUpdated(function ($state, $set) {
                        $set('slug', \Illuminate\Support\Str::slug($state));
                    }),

                Select::make('category_id')
                    ->label('الصنف')
                    ->relationship('category', 'name')
                    // ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),

                Select::make('meal_type')
                    ->options([
                        'breakfast' => 'فطور',
                        'lunch' => 'غداء',
                        'dinner' => 'عشاء',
                    ])
                    ->default('lunch')
                    ->required()
                    ->label('نوع الوجبة'),

                Textarea::make('description')
                    ->label('وصف الوجبة')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('price')
                        ->label('السعر')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->step(0.01),

                Toggle::make('is_available')
                        ->label('متاح للطلب')
                        ->default(true)
                        ->onColor('success')
                        ->offColor('danger'),

                FileUpload::make('image')
                    ->label('الصورة')
                    ->disk('public')
                    ->directory('meals')
                    ->image()
                    ->imageEditor()
                    ->maxSize(20480)
                    ->downloadable()
                    ->openable()
                    ->helperText('يمكنك رفع صورة للوجبة. الحد الأقصى للحجم 20 ميجابايت')
                    ->columnSpanFull()
                    ->afterStateUpdated(function ($state, $set, $get, $record) {
                        if ($record && $record->image && $state && $state != $record->image) {
                            Storage::disk('public')->delete($record->image);
                        }
                    })


            ]);
    }
}
