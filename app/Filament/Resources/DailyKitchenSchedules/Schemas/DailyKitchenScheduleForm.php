<?php

namespace App\Filament\Resources\DailyKitchenSchedules\Schemas;

use App\Models\Kitchen;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DailyKitchenScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        $currentKitchen = Auth::user()->kitchen;
        return $schema
            ->components([
                Section::make('معلومات الجدولة')
                    ->schema([

                        ...(Auth::user()->hasRole('kitchen') ? [

                            Hidden::make('kitchen_id')
                                ->default($currentKitchen->id),

                            Placeholder::make('current_institution')
                                ->label('المطبخ')
                                ->content($currentKitchen->name ?? 'غير معين')
                                ->extraAttributes(['class' => 'font-bold']),

                        ] : [


                            Select::make('kitchen_id')
                                ->label('المطبخ')
                                ->options(Kitchen::all()->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->columnSpanFull(),
                        ]) ,


                        DatePicker::make('schedule_date')
                            ->label('تاريخ الجدولة')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->minDate(now()->addDay()->toDateString())
                            ->default(now()->addDay()->toDateString())
                            ->displayFormat('d/m/Y') // تنسيق العرض
                            ->closeOnDateSelection() // إغلاق التقويم بعد الاختيار
                            ->columnSpanFull()
                            ->hint('يسمح فقط بالتواريخ المستقبلية')
                            ->hintColor('primary')
                            ->suffixIcon('heroicon-o-calendar')
                            ->extraAttributes([
                                'class' => 'text-lg font-bold'
                            ])
                            ->validationMessages([
                                'after' => 'لا يمكن جدولة تاريخ في الماضي!',
                            ]),


                    ]),

                Section::make('الوجبات المجدولة')
                    ->schema([
                        Repeater::make('scheduledMeals')
                            ->label('')
                            ->relationship('scheduledMeals')
                            ->schema([
                                Select::make('meal_id')
                                    ->label('الوجبة')
                                    ->relationship('meal', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(2),

                                Select::make('meal_type')
                                    ->label('نوع الوجبة')
                                    ->options([
                                        'breakfast' => 'فطور',
                                        'lunch' => 'غداء',
                                        'dinner' => 'عشاء',
                                    ])
                                    ->required()
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            // ->itemLabel(fn (array $state): ?string => $state['meal_id'] ?? null)
                            ->defaultItems(0)
                            ->collapsible()
                            ->cloneable()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (?Model $record) => $record !== null),
            ]);
    }
}
