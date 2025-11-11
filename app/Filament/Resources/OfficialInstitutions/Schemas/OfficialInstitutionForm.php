<?php

namespace App\Filament\Resources\OfficialInstitutions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OfficialInstitutionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المعلومات الأساسية')
                    ->description('المعلومات الأساسية للمؤسسة والعقد')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label('اسم المؤسسة')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                TextInput::make('contract_number')
                                    ->label('رقم العقد')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Select::make('institution_type')
                                    ->label('نوع المؤسسة')
                                    ->required()
                                    ->options([
                                        'scheduled' => 'جهة حكومية',
                                        'normal' => 'مؤسسة خاصة' ,
                                    ])
                                    ->default('normal')
                                    ->native(false),


                            ]),

                        Grid::make(3)
                            ->schema([
                                Select::make('contract_status')
                                    ->label('حالة العقد')
                                    ->required()
                                    ->options([
                                        'active' => 'نشط',
                                        'expired' => 'منتهي',
                                        'suspended' => 'موقوف',
                                        'renewed' => 'مجدد',
                                    ])
                                    ->default('active')
                                    ->native(false),
                                DatePicker::make('contract_start_date')
                                    ->label('تاريخ بداية العقد')
                                    ->required()
                                    ->native(false),

                                DatePicker::make('contract_end_date')
                                    ->label('تاريخ نهاية العقد')
                                    ->required()
                                    ->native(false)
                                    ->rule('after_or_equal:contract_start_date'),
                            ]),




                                TextInput::make('Financial_debts')
                                    ->label('الرصيد')
                                    ->numeric()
                                    ->hidden()
                                    ->default(0)
                                    ->required(),

                    ]),


                    Section::make('معلومات التواصل')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                // TextInput::make('contact_person')
                                //     ->label('الشخص المسؤول')
                                //     ->required()
                                //     ->maxLength(255),
                                 Select::make('user_id')
                                    ->label('الشخص المسؤول')
                                    ->relationship(
                                        name: 'user',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query) => $query->whereHas('roles', function ($q) {
                                            $q->where('name', 'institution');
                                        })
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                                    ->native(false),

                                TextInput::make('contact_phone')
                                    ->label('هاتف التواصل')
                                    ->tel()
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('contact_email')
                                    ->label('البريد الإلكتروني')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ]),


                    Section::make('معلومات إضافية')
                    ->schema([
                        Textarea::make('special_instructions')
                            ->label('تعليمات خاصة')
                            ->nullable()
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);

    }
}
