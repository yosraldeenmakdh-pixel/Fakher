<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('table_id')
                    ->preload()
                    ->relationship('table','name')
                    ->required(),
                Select::make('user_id')
                    ->required()
                    ->preload()
                    ->relationship('user','name'),

                TextInput::make('customer_name')
                    ->required(),
                TextInput::make('customer_phone')
                    ->tel()
                    ->required(),
                TextInput::make('guests_count')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('arrival_time')
                    ->required(),
                DateTimePicker::make('departure_time')
                    ->required()
                    ->reactive() // إضافة reactive
                ->afterStateUpdated(function ($state, $set, $get) {
                    // عند تحديث وقت المغادرة، احسب وقت الوجبة الفعلي تلقائياً
                    if ($state) {
                        $departureTime = Carbon::parse($state);
                        $actualMealEnd = $departureTime->copy()->addMinutes(30);
                        $set('actual_meal_end', $actualMealEnd->format('Y-m-d H:i:s'));
                    }
                }),
                DateTimePicker::make('actual_meal_end')
                    ->disabled() // جعله read-only لأنه محسوب تلقائياً
                    ->dehydrated() // للحفاظ على القيمة في قاعدة البيانات
                    ->required(),

                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'checked' => 'Checked',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
        ])
                    ->default('pending')
                    ->required(),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
