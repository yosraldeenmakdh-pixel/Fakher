<?php

namespace App\Filament\Resources\Complaints\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ComplaintForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المستخدم')
                    // ->description('بيانات المستخدم المرسل للشكوى')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Select::make('user_id')
                            ->label('المستخدم')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($record) => $record !== null)
                            ->helperText('اختر المستخدم للشكوى')
                            ->columnSpan(2),

                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->required()
                            ->tel()
                            ->maxLength(10)
                            ->prefixIcon('heroicon-o-phone')
                            // ->placeholder('09XXXXXXXX')
                            ->columnSpan(2),
                    ])->columns(2),

                Section::make('تفاصيل الشكوى')
                    // ->description('المحتوى الكامل للشكوى المقدمة')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextInput::make('subject')
                            ->label('موضوع الشكوى')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('أدخل موضوع الشكوى...')
                            ->columnSpanFull(),

                        Textarea::make('message')
                            ->label('نص الشكوى')
                            ->required()
                            ->rows(8)
                            ->placeholder('اكتب تفاصيل الشكوى هنا...')
                            ->columnSpanFull(),
                    ]),

                Section::make('ملاحظات الإدارة')
                    // ->description('ملاحظات خاصة بالإدارة للمتابعة')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsible()
                    ->schema([
                        Textarea::make('admin_notes')
                            ->label('ملاحظات الإدارة')
                            ->nullable()
                            ->rows(4)
                            // ->placeholder('أضف ملاحظات الإدارة هنا...')
                            ->helperText('هذه الملاحظات خاصة بالإدارة فقط ولن يراها المستخدم')
                            ->columnSpanFull(),
                    ]),

            ]);
    }
}
