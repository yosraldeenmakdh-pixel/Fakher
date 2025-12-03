<?php

namespace App\Filament\Resources\DailyKitchenSchedules\Schemas;

use App\Models\DailyKitchenSchedule;
use App\Models\Kitchen;
use App\Models\Meal;
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
use Illuminate\Validation\Rule;

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
                                // ->searchable()
                                ->required()
                                ->columnSpanFull(),
                        ]) ,


                        DatePicker::make('schedule_date')
                            ->label('تاريخ الجدولة')
                            ->required()
                            ->minDate(now()->addDay()->toDateString())
                            ->default(now()->addDay()->toDateString())
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->columnSpanFull()
                            ->hint('يسمح فقط بالتواريخ المستقبلية وغير المجدولة')
                            ->hintColor('primary')
                            ->suffixIcon('heroicon-o-calendar')
                            ->extraAttributes([
                                'class' => 'text-lg font-bold'
                            ])
                            ->rules([
                                'required',
                                'date',
                                'after:today',
                                function ($attribute, $value, $fail) {
                                    $kitchenId = request('kitchen_id',
                                        Auth::user()->kitchen->id ?? null
                                    );

                                    if (!$kitchenId) {
                                        $fail('يرجى تحديد المطبخ أولاً');
                                        return;
                                    }

                                    $recordId = request()->route('record')?->id;

                                    $exists = \App\Models\DailyKitchenSchedule::where('kitchen_id', $kitchenId)
                                        ->whereDate('schedule_date', $value)
                                        ->when($recordId, fn($q) => $q->where('id', '!=', $recordId))
                                        ->exists();

                                    if ($exists) {
                                        $fail('هذا التاريخ مجدول بالفعل لهذا المطبخ');
                                    }
                                }
                            ])
                            ->validationMessages([
                                'after' => 'لا يمكن جدولة تاريخ في الماضي!',
                            ])
                            // منع حفظ البيانات إذا كان هناك خطأ
                            ->dehydrated(fn ($state) => !empty($state))
                            ->saveRelationshipsUsing(null),

                    ]),

                Section::make('الوجبات المجدولة')
                    ->schema([
                        Repeater::make('scheduledMeals')
                            ->label('')
                            ->relationship('scheduledMeals')
                            ->schema([
                                Select::make('meal_type')
                                    ->label('نوع الوجبة')
                                    ->options([
                                        'breakfast' => 'فطور',
                                        'lunch' => 'غداء',
                                        'dinner' => 'عشاء',
                                    ])
                                    ->required()
                                    ->columnSpan(1)
                                    ->reactive() // هذا مهم لتفعيل التفاعل بين الحقول
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('meal_id', null)), // إعادة تعيين الوجبة عند تغيير النوع

                                Select::make('meal_id')
                                    ->label('الوجبة')
                                    ->options(function (callable $get) {
                                        $mealType = $get('meal_type');

                                        // إذا لم يتم اختيار نوع الوجبة، لا تعرض أي وجبات
                                        if (!$mealType) {
                                            return [];
                                        }

                                        // عرض الوجبات التي تطابق النوع المحدد
                                        return Meal::where('meal_type', $mealType)
                                            ->where('is_available', true)
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    // ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(2)
                                    ->disabled(fn (callable $get) => !$get('meal_type')) // تعطيل الحقل حتى يتم اختيار النوع
                                    ->helperText(function (callable $get) {
                                        $mealType = $get('meal_type');
                                        if (!$mealType) {
                                            return 'يرجى اختيار نوع الوجبة أولاً';
                                        }
                                        // return "يتم عرض وجبات ال{$mealType} فقط";
                                    }),
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
