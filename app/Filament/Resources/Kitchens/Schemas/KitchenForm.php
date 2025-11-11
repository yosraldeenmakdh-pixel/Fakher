<?php

namespace App\Filament\Resources\Kitchens\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KitchenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المعلومات الأساسية')
                    ->description('المعلومات الرئيسية للمطبخ')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم المطبخ')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('أدخل اسم المطبخ')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('الوصف')
                            ->required()
                            ->rows(3)
                            ->placeholder('وصف مختصر عن المطبخ'),



                        Select::make('user_id')
                                    ->label('الشخص المسؤول')
                                    ->relationship(
                                        name: 'user',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query) => $query->whereHas('roles', function ($q) {
                                            $q->where('name', 'kitchen');
                                        })
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                                    ->native(false),

                        Select::make('branch_id')
                            ->label('الفرع')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('معلومات التواصل')
                    ->description('تفاصيل التواصل مع المطبخ')
                    ->schema([
                        TextInput::make('contact_phone')
                            ->label('رقم الهاتف')
                            ->required()
                            ->tel()
                            ->maxLength(255) ,
                            // ->placeholder('+963XXXXXXXXX'),

                        TextInput::make('contact_email')
                            ->label('البريد الإلكتروني')
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->placeholder('email@example.com'),

                        Textarea::make('address')
                            ->label('العنوان')
                            ->required()
                            ->rows(2)
                            ->placeholder('العنوان الكامل للمطبخ'),
                    ])
                    ->columns(2),
                Section::make('أوقات العمل')
                    ->schema([
                        TimePicker::make('opening_time')
                            ->label('وقت الفتح')
                            ->required()
                            ->seconds(false),
                        TimePicker::make('closing_time')
                            ->label('وقت الإغلاق')
                            ->required()
                            ->seconds(false),
                    ])
                    ->columns(2),

                Section::make('المعلومات المالية والحالة')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->inline(false),

                        TextInput::make('Financial_debts')
                                    ->label('الرصيد')
                                    ->numeric()
                                    ->hidden()
                                    ->default(0)
                                    ->required(),
                    ])
                    ->columns(2),

            ]);
    }
}
