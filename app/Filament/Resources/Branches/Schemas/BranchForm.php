<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Models\Kitchen;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required(),

                Select::make('kitchen_id')
                    ->label('مطبخ الطلبات الخاصة')
                    ->relationship('kitchen', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('اختر المطبخ المرتبط بهذا الفرع من أجل الطلبات الخاصة'),

                FileUpload::make('image')
                            ->label('الصورة')
                            ->disk('public')
                            ->directory('branches')
                            ->image()
                            ->imageEditor()
                            ->maxSize(20480)
                            ->downloadable()
                            ->openable()
                            ->helperText('الحد الأقصى لحجم الصورة 20 ميجابايت')
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state, $set, $get, $record) {
                                if ($record && $record->image && $state && $state != $record->image) {
                                    Storage::disk('public')->delete($record->image);
                                }
                            }) ,
                TextInput::make('description')
                    ->label('الوصف')
            ]);
    }
}
