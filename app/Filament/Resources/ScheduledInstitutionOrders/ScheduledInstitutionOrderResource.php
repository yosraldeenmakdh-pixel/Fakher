<?php

namespace App\Filament\Resources\ScheduledInstitutionOrders;

use App\Filament\Resources\ScheduledInstitutionOrders\Pages\CreateScheduledInstitutionOrder;
use App\Filament\Resources\ScheduledInstitutionOrders\Pages\EditScheduledInstitutionOrder;
use App\Filament\Resources\ScheduledInstitutionOrders\Pages\ListScheduledInstitutionOrders;
use App\Filament\Resources\ScheduledInstitutionOrders\Schemas\ScheduledInstitutionOrderForm;
use App\Filament\Resources\ScheduledInstitutionOrders\Tables\ScheduledInstitutionOrdersTable;
use App\Models\ScheduledInstitutionOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScheduledInstitutionOrderResource extends Resource
{
    protected static ?string $model = ScheduledInstitutionOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ScheduledInstitutionOrderForm::configure($schema);
    }
    public static function getNavigationGroup(): ?string
    {
        return __('Institutions');
    }

    public static function table(Table $table): Table
    {
        return ScheduledInstitutionOrdersTable::configure($table);
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
            'index' => ListScheduledInstitutionOrders::route('/'),
            'create' => CreateScheduledInstitutionOrder::route('/create'),
            'edit' => EditScheduledInstitutionOrder::route('/{record}/edit'),
        ];
    }
}
