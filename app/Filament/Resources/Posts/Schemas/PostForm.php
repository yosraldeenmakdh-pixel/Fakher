<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المعلومات الأساسية')
                    ->schema([
                        Select::make('type')
                            ->label('نوع المحتوى')
                            ->options([
                                'news' => 'خبر',
                                'article' => 'مقال',
                            ])
                            ->required()
                            ->default('news')
                            ->reactive()
                            ->native(false),

                        TextInput::make('title')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                    ])->columns(2),

                Section::make('المحتوى')
                    ->schema([
                        Textarea::make('summary')
                            ->label('الملخص')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->label('المحتوى')
                            ->required()
                            ->fileAttachmentsDirectory('posts')
                            ->columnSpanFull(),
                    ]),

                Section::make('الصورة والإعدادات')
                    ->schema([
                        FileUpload::make('image')
                            ->label('الصورة الرئيسية')
                            ->disk('public')
                            ->directory('posts')
                            ->image()
                            ->imageEditor()
                            ->columnSpan(1)

                            ->maxSize(20480)
                            ->downloadable()
                            ->openable()
                            ->helperText('Maximum file size: 20MB. Allowed formats: JPG, PNG, GIF')
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state, $set, $get, $record) {
                                if ($record && $record->image && $state && $state != $record->image) {
                                    Storage::disk('public')->delete($record->image);
                                }
                            }) ,

                        Toggle::make('is_published')
                            ->label('منشور')
                            ->default(true)
                            ->columnSpan(1),
                    ])->columns(2),
            ]);
    }
}
