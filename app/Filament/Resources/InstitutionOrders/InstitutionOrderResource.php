<?php

namespace App\Filament\Resources\InstitutionOrders;

use App\Filament\Resources\InstitutionOrders\Pages\CreateInstitutionOrder;
use App\Filament\Resources\InstitutionOrders\Pages\EditInstitutionOrder;
use App\Filament\Resources\InstitutionOrders\Pages\ListInstitutionOrders;
use App\Filament\Resources\InstitutionOrders\Schemas\InstitutionOrderForm;
use App\Filament\Resources\InstitutionOrders\Tables\InstitutionOrdersTable;
use App\Models\InstitutionOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InstitutionOrderResource extends Resource
{
    protected static ?string $model = InstitutionOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return InstitutionOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstitutionOrdersTable::configure($table);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Institutions');
    }



    public static function beforeCreate(array $data): array
    {
        return self::updateFinalTotals($data);
    }

    // وإذا كنت تريد نفس الشيء للتحديث أيضاً
    public static function beforeUpdate(array $data): array
    {
        return self::updateFinalTotals($data);
    }

    private static function updateFinalTotals(array $data): array
    {
        $items = $data['orderItems'] ?? [];
        $totalAmount = 0;

        foreach ($items as $index => $item) {
            $quantity = (int)($item['quantity'] ?? 1);
            $unitPrice = (float)($item['unit_price'] ?? 0);
            $totalPrice = $quantity * $unitPrice;

            // تحديث السعر الإجمالي لكل وجبة
            $data['orderItems'][$index]['total_price'] = number_format($totalPrice, 2, '.', '');
            $totalAmount += $totalPrice;
        }

        // تحديث المبلغ الإجمالي للطلب
        $data['total_amount'] = $totalAmount;

        return $data;
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
            'index' => ListInstitutionOrders::route('/'),
            'create' => CreateInstitutionOrder::route('/create'),
            'edit' => EditInstitutionOrder::route('/{record}/edit'),
        ];
    }
}
