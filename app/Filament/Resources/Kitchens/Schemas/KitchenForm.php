<?php

namespace App\Filament\Resources\Kitchens\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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

                        Select::make('user_id')
                            ->label('الشخص المسؤول')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->whereHas('roles', function ($q) {
                                    $q->where('name', 'kitchen');
                                })->whereDoesntHave('kitchen')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
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
                    ])
                    ->columns(2),

            ]);
    }
}
