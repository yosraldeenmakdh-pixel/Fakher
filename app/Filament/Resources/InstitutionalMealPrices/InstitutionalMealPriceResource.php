<?php

namespace App\Filament\Resources\InstitutionalMealPrices;

use App\Filament\Resources\InstitutionalMealPrices\Pages\CreateInstitutionalMealPrice;
use App\Filament\Resources\InstitutionalMealPrices\Pages\EditInstitutionalMealPrice;
use App\Filament\Resources\InstitutionalMealPrices\Pages\ListInstitutionalMealPrices;
use App\Filament\Resources\InstitutionalMealPrices\Schemas\InstitutionalMealPriceForm;
use App\Filament\Resources\InstitutionalMealPrices\Tables\InstitutionalMealPricesTable;
use App\Models\InstitutionalMealPrice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InstitutionalMealPriceResource extends Resource
{
    protected static ?string $model = InstitutionalMealPrice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return InstitutionalMealPriceForm::configure($schema);
    }
    public static function getNavigationGroup(): ?string
    {
        return __('Institutions');
    }

    public static function table(Table $table): Table
    {
        return InstitutionalMealPricesTable::configure($table);
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
            'index' => ListInstitutionalMealPrices::route('/'),
            'create' => CreateInstitutionalMealPrice::route('/create'),
            'edit' => EditInstitutionalMealPrice::route('/{record}/edit'),
        ];
    }
}
