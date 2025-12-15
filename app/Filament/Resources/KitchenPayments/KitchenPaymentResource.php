<?php

namespace App\Filament\Resources\KitchenPayments;

use App\Filament\Resources\KitchenPayments\Pages\CreateKitchenPayment;
use App\Filament\Resources\KitchenPayments\Pages\EditKitchenPayment;
use App\Filament\Resources\KitchenPayments\Pages\ListKitchenPayments;
use App\Filament\Resources\KitchenPayments\Schemas\KitchenPaymentForm;
use App\Filament\Resources\KitchenPayments\Tables\KitchenPaymentsTable;
use App\Models\KitchenPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KitchenPaymentResource extends Resource
{
    protected static ?string $model = KitchenPayment::class;

    protected static ?string $navigationLabel = 'الدفعات';
    protected static ?string $pluralModelLabel = 'الدفعات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return __('ادارة المطابخ');
    }

    public static function form(Schema $schema): Schema
    {
        return KitchenPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KitchenPaymentsTable::configure($table);
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
            'index' => ListKitchenPayments::route('/'),
            'create' => CreateKitchenPayment::route('/create'),
            'edit' => EditKitchenPayment::route('/{record}/edit'),
        ];
    }
}
