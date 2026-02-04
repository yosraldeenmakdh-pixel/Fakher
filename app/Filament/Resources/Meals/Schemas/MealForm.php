<?php

namespace App\Filament\Resources\Meals\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class MealForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم الوجبة')
                    ->required()
                    ->afterStateUpdated(function ($state, $set) {
                        $set('slug', \Illuminate\Support\Str::slug($state));
                    }),

                Select::make('category_id')
                    ->label('الصنف')
                    ->relationship('category', 'name')
                    // ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),

                Select::make('meal_type')
                    ->options([
                        'breakfast' => 'فطور',
                        'lunch' => 'غداء',
                        'dinner' => 'عشاء',
                    ])
                    ->default('lunch')
                    ->required()
                    ->label('نوع الوجبة'),

                Textarea::make('description')
                    ->label('وصف الوجبة')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('preparation_minutes')
                    ->label('وقت التحضير (دقيقة)')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    // ->maxValue(300)
                    ->default(15)
                    ->suffix('دقيقة')
                    ->helperText('الوقت التقريبي لتحضير هذه الوجبة'),

                TextInput::make('price')
                        ->label('السعر')
                        ->required()
                        ->numeric()
                        // ->prefix('ل.س')
                        ->step(0.01),

                Toggle::make('is_available')
                        ->label('متاح للطلب')
                        ->default(true)
                        ->onColor('success')
                        ->offColor('danger'),

                FileUpload::make('image')
                    ->label('الصورة')
                    ->disk('public')
                    ->directory('meals')
                    ->image()
                    ->imageEditor()
                    ->maxSize(20480)
                    ->downloadable()
                    ->openable()
                    ->helperText('يمكنك رفع صورة للوجبة. الحد الأقصى للحجم 20 ميجابايت')
                    ->columnSpanFull()
                    ->afterStateUpdated(function ($state, $set, $get, $record) {
                        if ($record && $record->image && $state && $state != $record->image) {
                            Storage::disk('public')->delete($record->image);
                        }
                    })

                // Section::make('وسائط الوجبة')
                //     ->description('أضف صور وفيديوهات للوجبة')
                //     ->collapsible()
                //     ->schema([
                //         Repeater::make('media')
                //             ->relationship('media')
                //             ->label('')
                //             ->reorderable()
                //             ->cloneable()
                //             ->collapsible()
                //             ->itemLabel(fn (array $state): ?string =>
                //                 $state['file_url'] ?? 'وسيط جديد'
                //             )
                //             ->schema([
                //                 FileUpload::make('file_url')
                //                     ->label('الملف')
                //                     ->required()
                //                     ->disk('public')
                //                     ->directory('meal-media')
                //                     ->preserveFilenames()
                //                     ->acceptedFileTypes(['image/*', 'video/*'])
                //                     ->maxSize(30720)
                //                     ->multiple() // إضافة هذه الخاصية
                //                     ->maxFiles(10) // الحد الأقصى
                //                     ->reorderable()
                //                     ->storeFileNamesIn('original_filenames') // هذه مهمة
                //                     ->uploadingMessage('جاري الرفع...')
                //                     ->uploadProgressIndicatorPosition('left')
                //                     ->visibility('public'),

                //                 Select::make('type')
                //                     ->label('النوع')
                //                     ->options([
                //                         'image' => 'صورة',
                //                         'video' => 'فيديو',
                //                     ])
                //                     ->required()
                //                     ->default('image'),

                //                 Toggle::make('is_primary')
                //                     ->label('صورة رئيسية'),

                //                 TextInput::make('order')
                //                     ->label('الترتيب')
                //                     ->numeric()
                //                     ->default(0),
                //             ])
                //             ->defaultItems(0)
                //             ->columnSpanFull(),
                //     ]),

            ]);
    }
}
