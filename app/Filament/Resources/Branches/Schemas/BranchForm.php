<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                        TextInput::make('name')
                            ->label('Branch Name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Enter branch name'),

                        Textarea::make('address')
                            ->label('Address')
                            ->required()
                            ->rows(3)
                            ->placeholder('Enter full address'),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->required()
                            ->tel()
                            ->prefix('+')
                            ->placeholder('Enter phone number'),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->required()
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->placeholder('example@domain.com'),

                        TimePicker::make('opening_time')
                            ->label('Opening Time')
                            ->required()
                            ->seconds(false),

                        TimePicker::make('closing_time')
                            ->label('Closing Time')
                            ->required()
                            ->seconds(false)
                            ->after('opening_time'),
        ]);
    }
}
