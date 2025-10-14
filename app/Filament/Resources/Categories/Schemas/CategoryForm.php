<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                        TextInput::make('name')
                            ->label('Category Name')
                            ->required()
                            ->placeholder('Enter category name'),

                        Textarea::make('description')
                            ->label('Description')
                            ->nullable()
                            ->placeholder('Enter category description'),



                        FileUpload::make('image')
                            ->label('Image')
                            ->disk('public')
                            ->directory('categories')
                            ->image()
                            ->imageEditor()

                            ->maxSize(20480)
                            ->downloadable()
                            ->openable()
                            ->helperText('Maximum file size: 20MB. Allowed formats: JPG, PNG, GIF')
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state, $set, $get, $record) {
                                if ($record && $record->image && $state && $state != $record->image) {
                                    Storage::disk('public')->delete($record->image);
                                }
                            })

            ]) ;

    }
}
