<?php

namespace App\Filament\Resources\InstitutionPayments;

use App\Filament\Resources\InstitutionPayments\Pages\CreateInstitutionPayment;
use App\Filament\Resources\InstitutionPayments\Pages\EditInstitutionPayment;
use App\Filament\Resources\InstitutionPayments\Pages\ListInstitutionPayments;
use App\Filament\Resources\InstitutionPayments\Schemas\InstitutionPaymentForm;
use App\Filament\Resources\InstitutionPayments\Tables\InstitutionPaymentsTable;
use App\Models\InstitutionPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InstitutionPaymentResource extends Resource
{
    protected static ?string $model = InstitutionPayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return InstitutionPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstitutionPaymentsTable::configure($table);
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
            'index' => ListInstitutionPayments::route('/'),
            'create' => CreateInstitutionPayment::route('/create'),
            'edit' => EditInstitutionPayment::route('/{record}/edit'),
        ];
    }
}
