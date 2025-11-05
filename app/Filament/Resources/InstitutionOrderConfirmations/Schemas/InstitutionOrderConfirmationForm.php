<?php

namespace App\Filament\Resources\InstitutionOrderConfirmations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class InstitutionOrderConfirmationForm
{



    public static function configure(Schema $schema): Schema
    {

        $isKitchen = Auth::user()->hasRole('kitchen');

        return $schema
            ->components([
                Section::make('معلومات التأكيد')
                    ->description('تفاصيل تأكيد الطلب من قبل المطبخ')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('order_number')
                                    ->label('رقم الطلب')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled(true)
                                    ->prefixIcon('heroicon-o-document'),



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

                                    // ->disabled(fn() => $isKitchen && in_array($this->status ?? 'confirmed', ['delivered', 'cancelled']))
                                    // ->afterStateUpdated(function ($state, $set) {
                                    //     if ($state === 'delivered') {
                                    //         $set('delivered_at', now());
                                    //     }
                                    // }),

                            ]),

                        Grid::make(2)
                            ->schema([
                            DatePicker::make('delivery_date')
                                    ->label('تاريخ التسليم')
                                    ->required()
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->disabled($isKitchen),

                                TimePicker::make('delivery_time')
                                    ->label('وقت التسليم')
                                    ->required()
                                    ->seconds(false)
                                    ->prefixIcon('heroicon-o-clock')
                                    ->disabled($isKitchen),
                            ]),

                        TextInput::make('total_amount')
                            ->label('المبلغ الإجمالي')
                            ->required()
                            ->numeric()
                            // ->prefix('د.ك')
                            ->prefixIcon('heroicon-o-currency-dollar')
                            ->visible(!$isKitchen),

                        Textarea::make('special_instructions')
                            ->label('تعليمات خاصة')
                            ->nullable()
                            ->rows(3)
                            ->placeholder('أي تعليمات خاصة بالطلب...')
                            ->disabled($isKitchen),
                    ])
                    ->collapsible(),

                Section::make('معلومات المطبخ والملاحظات')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('kitchen_id')
                                    ->label('المطبخ')
                                    ->relationship('kitchen', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->prefixIcon('heroicon-o-home')
                                    ->disabled($isKitchen),

                                DateTimePicker::make('delivered_at')
                                    ->label('وقت التسليم الفعلي')
                                    ->nullable()
                                    ->prefixIcon('heroicon-o-check-badge')
                                    ->disabled($isKitchen),
                            ]),

                        Textarea::make('notes')
                            ->label('ملاحظات المطبخ')
                            ->nullable()
                            ->rows(4)
                            ->placeholder('ملاحظات إضافية من المطبخ...')
                            ->disabled($isKitchen),

                            // ->helperText('يمكن للمطبخ إضافة ملاحظات حول الطلب أو التحضير'),
                    ])
                    ->collapsible(),
            ]);
    }
}
