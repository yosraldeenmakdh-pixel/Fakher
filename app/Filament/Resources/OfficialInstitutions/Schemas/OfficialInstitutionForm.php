<?php

namespace App\Filament\Resources\OfficialInstitutions\Schemas;

use App\Models\Kitchen;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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

                                Select::make('branch_id')
                                    ->label('الفرع')
                                    ->relationship('branch', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->live(), // إضافة live لتحديث الحقول المعتمدة عليه عند التغيير

                                Select::make('kitchen_id')
                                    ->label('المطبخ')
                                    ->options(function (Get $get, Set $set) {
                                        $branchId = $get('branch_id');
                                        if (!$branchId) {
                                            return [];
                                        }
                                        return Kitchen::where('branch_id', $branchId)->pluck('name', 'id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false) ,

                            ]) ,

                            TextInput::make('Financial_debts')
                                ->label('الرصيد')
                                ->numeric()
                                ->hidden()
                                ->default(0)
                                ->required(),

                    ]),

                    Section::make('معلومات العقد')
                        ->schema([
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
                            ]),


                    Section::make('معلومات التواصل')
                    ->schema([
                        Grid::make(3)
                            ->schema([
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
