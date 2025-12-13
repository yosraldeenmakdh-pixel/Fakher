<?php

namespace App\Filament\Resources\Ratings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RatingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('معلومات التقييم')
                    ->icon('heroicon-o-information-circle')
                    ->description('معلومات أساسية عن التقييم')
                    ->schema([
                        Select::make('user_id')
                            ->label('المستخدم')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('اختر المستخدم')
                            ->native(false)
                            ->columnSpan(1)
                            ->helperText('المستخدم الذي قام بالتقييم'),

                        Select::make('meal_id')
                            ->label('الوجبة')
                            ->relationship('meal', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('اختر الوجبة')
                            ->native(false)
                            ->columnSpan(1)
                            ->helperText('الوجبة التي تم تقييمها'),

                        Select::make('rating')
                            ->label('التقييم')
                            ->options([
                                1 => '⭐ - ضعيف',
                                2 => '⭐⭐ - مقبول',
                                3 => '⭐⭐⭐ - جيد',
                                4 => '⭐⭐⭐⭐ - جيد جداً',
                                5 => '⭐⭐⭐⭐⭐ - ممتاز',
                            ])
                            ->required()
                            ->native(false)
                            ->selectablePlaceholder(false)
                            ->columnSpan(2)
                            ->helperText('اختر عدد النجوم من 1 إلى 5'),

                        Textarea::make('comment')
                            ->label('التعليق')
                            ->nullable()
                            ->rows(4)
                            ->placeholder('أضف تعليقك هنا...')
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->helperText('يمكنك إضافة تعليق يصل إلى 1000 حرف'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('إعدادات العرض')
                    ->icon('heroicon-o-eye')
                    ->description('التحكم في عرض التقييم في الموقع')
                    ->schema([
                        Toggle::make('is_visible')
                            ->label('ظاهر للعموم')
                            ->default(true)
                            ->inline(false)
                            ->onColor('success')
                            ->offColor('danger')
                            ->helperText('سيظهر هذا التقييم في الموقع إذا تم تفعيله'),
                    ])
                    ->collapsible(),

            ]);
    }
}
