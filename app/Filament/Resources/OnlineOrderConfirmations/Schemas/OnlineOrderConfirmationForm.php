<?php

namespace App\Filament\Resources\OnlineOrderConfirmations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class OnlineOrderConfirmationForm
{
    public static function configure(Schema $schema): Schema
    {
        $isKitchen = Auth::user()->hasRole('kitchen');

        return $schema
            ->components([
                TextInput::make('order_id')
                    ->hidden($isKitchen)
                    ->required()
                    ->numeric(),
                TextInput::make('kitchen_id')
                    ->hidden($isKitchen)
                    ->required()
                    ->numeric(),
                Textarea::make('notes')
                    ->default(null)
                    ->disabled($isKitchen)
                    ->columnSpanFull(),
                TextInput::make('order_number')
                    ->disabled($isKitchen)
                    ->required(),
                DateTimePicker::make('delivery_date')
                    ->disabled($isKitchen)
                    ->required(),
                TextInput::make('total_amount')
                    ->required()
                    ->hidden($isKitchen)
                    ->numeric(),
                // Select::make('status')
                //     ->options(['confirmed' => 'Confirmed', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'])
                //     ->required(),
                ...(Auth::user()->hasRole('kitchen') ? [
                    Select::make('status')
                    ->label('حالة الطلب')
                    ->required()
                    ->default('confirmed')
                    ->options([

                        'delivered' => '📦 تم التسليم',

                    ])
                    ->native(false)
                ]:[
                    Select::make('status')
                    ->label('حالة الطلب')
                    ->required()
                    ->default('confirmed')
                    ->options([
                        'pending' => '⏳ معلق',
                        'confirmed' => '✅ مؤكد',
                        'delivered' => '📦 تم التسليم',
                        'cancelled' => '❌ ملغي',
                    ])
                    ->native(false)
                    ->reactive(),
                ]) ,
                Textarea::make('special_instructions')
                    ->disabled($isKitchen)
                    ->default(null)
                    ->columnSpanFull(),
                DateTimePicker::make('delivered_at')
                ->hidden($isKitchen),
            ]);
    }
}
