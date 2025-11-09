<?php

namespace App\Filament\Resources\ContactSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ContactSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('المفتاح')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn ($record) => $record !== null),

                TextInput::make('label')
                    ->label('الوصف')
                    ->required()
                    ->maxLength(255),

                Textarea::make('value')
                    ->label('القيمة')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
