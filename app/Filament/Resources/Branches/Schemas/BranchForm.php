<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\FileUpload;
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
