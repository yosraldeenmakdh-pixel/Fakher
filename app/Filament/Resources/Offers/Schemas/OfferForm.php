<?php

namespace App\Filament\Resources\Offers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class OfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات العرض الأساسية')
                    ->description('أدخل المعلومات الأساسية للعرض')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم العرض')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: عرض الصيف المميز')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('وصف العرض')
                            ->placeholder('وصف تفصيلي للعرض وشروطه...')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('discount_value')
                            ->label('قيمة الخصم')
                            // ->numeric()
                            // ->prefix('ريال')
                            ->placeholder('0.00') ,
                            // ->minValue(0)
                            // ->maxValue(999999.99),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('صورة العرض')
                    ->description('رفع صورة مميزة للعرض')
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
                            ->helperText('Maximum file size: 20MB. Allowed formats: JPG, PNG, GIF')
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state, $set, $get, $record) {
                                if ($record && $record->image && $state && $state != $record->image) {
                                    Storage::disk('public')->delete($record->image);
                                }
                            }) ,
                    ])
                    ->collapsible(),

                Section::make('إعدادات العرض')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('العرض نشط')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false)
                    ])
                    ->compact(),
            ]);
    }
}
