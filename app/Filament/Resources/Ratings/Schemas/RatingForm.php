<?php

namespace App\Filament\Resources\Ratings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RatingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->required()
                    ->preload()
                    ->relationship('user','name') ,
                Select::make('meal_id')
                    ->required()
                    ->preload()
                    ->relationship('meal','name') ,
                TextInput::make('rating')
                    ->required()
                    ->numeric(),
                Textarea::make('comment')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('is_visible')
                    ->required(),
            ]);
    }
}
