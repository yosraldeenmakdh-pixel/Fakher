<?php

namespace App\Filament\Resources\InstitutionOrderConfirmations;

use App\Filament\Resources\InstitutionOrderConfirmations\Pages\CreateInstitutionOrderConfirmation;
use App\Filament\Resources\InstitutionOrderConfirmations\Pages\EditInstitutionOrderConfirmation;
use App\Filament\Resources\InstitutionOrderConfirmations\Pages\ListInstitutionOrderConfirmations;
use App\Filament\Resources\InstitutionOrderConfirmations\Schemas\InstitutionOrderConfirmationForm;
use App\Filament\Resources\InstitutionOrderConfirmations\Tables\InstitutionOrderConfirmationsTable;
use App\Models\InstitutionOrderConfirmation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InstitutionOrderConfirmationResource extends Resource
{
    protected static ?string $model = InstitutionOrderConfirmation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return InstitutionOrderConfirmationForm::configure($schema);
    }
    public static function getNavigationGroup(): ?string
    {
        return __('Institutions');
    }
    public static function table(Table $table): Table
    {
        return InstitutionOrderConfirmationsTable::configure($table);
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
            'index' => ListInstitutionOrderConfirmations::route('/'),
            'create' => CreateInstitutionOrderConfirmation::route('/create'),
            'edit' => EditInstitutionOrderConfirmation::route('/{record}/edit'),
        ];
    }
}
