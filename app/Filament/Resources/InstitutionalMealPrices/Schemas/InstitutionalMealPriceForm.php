<?php

namespace App\Filament\Resources\InstitutionalMealPrices\Schemas;

use App\Models\Meal;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class InstitutionalMealPriceForm
{
    public static function configure(Schema $schema)
    {
        return $schema
            ->components([

                Section::make('معلومات الوجبة')
                    ->schema([
                        Select::make('meal_id')
                            ->label('الوجبة')
                            ->relationship('meal', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $meal = Meal::find($state);
                                    if ($meal) {
                                        $set('base_price_display', $meal->price ?? 0);
                                    }
                                }
                            })
                            ->columnSpanFull(),

                        Grid::make()
                            ->schema([
                                Placeholder::make('base_price_display')
                                    ->label('السعر العام')
                                    ->content(function ($get, $set) {
                                        $mealId = $get('meal_id');
                                        if ($mealId) {
                                            $meal = Meal::find($mealId);
                                            return $meal ? number_format($meal->price, 2) . ' $' : '0.00 $';
                                        }
                                        return '0.00 $';
                                    })
                                    ->extraAttributes(['class' => 'text-success font-bold text-lg']),

                                TextInput::make('scheduled_price')
                                    ->label('السعر الخاص للمؤسسات')
                                    ->required()
                                    ->numeric()
                                    ->reactive()
                                    ->hint('يجب أن يكون أقل من السعر العام ')
                                    ->hintColor('primary')
                                    ->extraAttributes(['class' => 'font-bold']),
                            ])->columns(1),
                    ]),

                Section::make('إعدادات السعر')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('تفعيل السعر المؤسسي')
                            ->default(true)
                            ->required()
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),

                        Placeholder::make('discount_calculation')
                            ->label('نسبة الخصم')
                            ->content(function ($get) {
                                $mealId = $get('meal_id');
                                $scheduledPrice = $get('scheduled_price');

                                if ($mealId && $scheduledPrice) {
                                    $meal = Meal::find($mealId);
                                    if ($meal && $meal->price > 0) {
                                        $discount = (($meal->price - $scheduledPrice) / $meal->price) * 100;
                                        return number_format($discount, 1) . ' %';
                                    }
                                }
                                return '0 %';
                            })
                            ->extraAttributes(['class' => 'text-danger font-bold']),
                    ])->columns(2),

                Section::make('معلومات إضافية')
                    ->schema([
                        Placeholder::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->content(fn (?Model $record): string => $record?->created_at ? $record->created_at->format('d/m/Y H:i') : '-'),

                        Placeholder::make('updated_at')
                            ->label('آخر تحديث')
                            ->content(fn (?Model $record): string => $record?->updated_at ? $record->updated_at->format('d/m/Y H:i') : '-'),
                    ])->columns(2)
                    ->visible(fn (?Model $record) => $record !== null),

            ]);
    }
}
