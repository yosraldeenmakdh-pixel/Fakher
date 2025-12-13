<?php

namespace App\Filament\Resources\Kitchens;

use App\Filament\Resources\Kitchens\Pages\CreateKitchen;
use App\Filament\Resources\Kitchens\Pages\EditKitchen;
use App\Filament\Resources\Kitchens\Pages\ListKitchens;
use App\Filament\Resources\Kitchens\Schemas\KitchenForm;
use App\Filament\Resources\Kitchens\Tables\KitchensTable;
use App\Models\Kitchen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KitchenResource extends Resource
{
    protected static ?string $model = Kitchen::class;

    protected static ?string $navigationLabel = 'المطابخ';
    protected static ?string $pluralModelLabel = 'المطابخ';
    // protected static ?string $modelLabel = 'مطبخ';


    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return KitchenForm::configure($schema);
    }
    public static function getNavigationGroup(): ?string
    {
        return __('المطابخ');
    }

    public static function table(Table $table): Table
    {
        return KitchensTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKitchens::route('/'),
            'create' => CreateKitchen::route('/create'),
            'edit' => EditKitchen::route('/{record}/edit'),
        ];
    }
}
