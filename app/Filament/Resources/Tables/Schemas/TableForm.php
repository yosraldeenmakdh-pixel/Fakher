<?php

namespace App\Filament\Resources\Tables\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

use function Laravel\Prompts\select;

class TableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                select::make('kitchen_id')
                    ->required()
                    ->relationship('kitchen', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
                TextInput::make('name')
                    ->required(),
                TextInput::make('capacity')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options(['available' => 'Available', 'unavailable' => 'Unavailable', 'maintenance' => 'Maintenance'])
                    ->default('available')
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
