<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }
    public static function getNavigationGroup(): ?string
    {
        return __('Orders from kitchem');
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
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
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }

    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }

}
