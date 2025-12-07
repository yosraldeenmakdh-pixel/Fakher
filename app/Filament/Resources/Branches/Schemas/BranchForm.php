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
                    ->required(),

                Select::make('kitchen_id')
                            ->label('مطبخ الطلبات الخاصة')
                            ->relationship('kitchen', 'name')
                            ->searchable()
                            ->preload()
                            // ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('اسم المطبخ')
                                    ->required(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return Kitchen::create($data)->id;
                            })
                            ->helperText('اختر المطبخ المرتبط بهذا الفرع من أجل الطلبات الخاصة'),

                FileUpload::make('image')
                            ->label('Image')
                            ->disk('public')
                            ->directory('branches')
                            ->image()
                            ->imageEditor()

                            ->maxSize(20480)
                            ->downloadable()
                            ->openable()
                            ->helperText('Maximum file size: 20MB. Allowed formats: JPG, PNG, GIF')
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state, $set, $get, $record) {
                                if ($record && $record->image && $state && $state != $record->image) {
                                    Storage::disk('public')->delete($record->image);
                                }
                            }) ,
                TextInput::make('description')
                    ->required(),
            ]);
    }
}
