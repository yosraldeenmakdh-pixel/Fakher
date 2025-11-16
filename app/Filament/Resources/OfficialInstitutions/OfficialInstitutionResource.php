<?php

namespace App\Filament\Resources\OfficialInstitutions;

use App\Filament\Resources\OfficialInstitutions\Pages\CreateOfficialInstitution;
use App\Filament\Resources\OfficialInstitutions\Pages\EditOfficialInstitution;
use App\Filament\Resources\OfficialInstitutions\Pages\ListOfficialInstitutions;
use App\Filament\Resources\OfficialInstitutions\Schemas\OfficialInstitutionForm;
use App\Filament\Resources\OfficialInstitutions\Tables\OfficialInstitutionsTable;
use App\Models\OfficialInstitution;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OfficialInstitutionResource extends Resource
{
    protected static ?string $model = OfficialInstitution::class;
    protected static ?int $navigationSort = 1;


    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';



    public static function form(Schema $schema): Schema
    {
        return OfficialInstitutionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfficialInstitutionsTable::configure($table);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Institutions');
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
            'index' => ListOfficialInstitutions::route('/'),
            'create' => CreateOfficialInstitution::route('/create'),
            'edit' => EditOfficialInstitution::route('/{record}/edit'),

        ];
    }
}
