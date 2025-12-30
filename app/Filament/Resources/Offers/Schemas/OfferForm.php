<?php

namespace App\Filament\Resources\Offers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
// use Filament\Forms\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use App\Models\Meal;
use Filament\Schemas\Components\Section;

class OfferForm
{
    public static function configure(Schema $schema, $record = null): Schema
    {
        // تحميل البيانات الحالية للعرض (للتعديل)
        $currentCategory = $record ? $record->categories()->first() : null;
        $currentMeals = $record ? $record->meals()->pluck('meals.id')->toArray() : [];

        return $schema
            ->components([
                Section::make('معلومات العرض الأساسية')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم العرض')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('وصف العرض')
                            ->placeholder('وصف تفصيلي للعرض وشروطه...')
                            ->rows(3)
                            ->columnSpanFull(),

                        Grid::make()
                            ->schema([
                                TextInput::make('discount_value')
                                    ->label('قيمة الخصم')
                                    ->placeholder('مثال: 10% أو 5$ أو 15')
                                    ->helperText('أدخل النسبة المئوية بعلامة % أو قيمة ثابتة بعلامة $')
                                    ->required(),

                                Toggle::make('is_active')
                                    ->label('العرض نشط')
                                    ->default(true)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->inline(false),
                            ])
                            ->columns(2),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('ربط العرض')
                    ->description('اختر طريقة ربط العرض - يمكنك ربطه بصنف كامل أو وجبات محددة')
                    ->schema([
                        Select::make('link_type')
                            ->label('نوع الربط')
                            ->options([
                                'category' => 'ربط بصنف كامل',
                                'meals' => 'ربط بوجبات محددة',
                            ])
                            ->default('category')
                            ->reactive()
                            ->required(),

                        Select::make('category_id')
                            ->label('الصنف')
                            ->options(Category::pluck('name', 'id')) // تم إزالة شرط is_active
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('link_type') == 'category')
                            ->default($currentCategory ? $currentCategory->id : null)
                            ->helperText('سيتم تطبيق العرض على جميع الوجبات في هذا الصنف'),

                        Select::make('meal_ids')
                            ->label('الوجبات')
                            ->multiple()
                            ->options(Meal::where('is_available', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('link_type') == 'meals')
                            ->default($currentMeals)
                            ->helperText('اختر الوجبات المحددة التي سيتم تطبيق العرض عليها'),
                    ])
                    ->collapsible(),

                Section::make('صورة العرض')
                    ->description('رفع صورة مميزة للعرض (اختياري)')
                    ->schema([
                        FileUpload::make('image')
                            ->label('صورة العرض')
                            ->image()
                            ->disk('public')
                            ->directory('offers')
                            ->maxSize(20480)
                            ->imageEditor()
                            ->downloadable()
                            ->openable()
                            ->helperText('الحد الأقصى لحجم الصورة : 20 ميجابايت')
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state, $set, $get, $record) {
                                if ($record && $record->image && $state && $state != $record->image) {
                                    Storage::disk('public')->delete($record->image);
                                }
                            }),
                    ])
                    ->collapsible(),
            ]);
    }
}
