<?php

namespace App\Filament\Resources\OrderOnlines;

use App\Filament\Resources\OrderOnlines\Pages\CreateOrderOnline;
use App\Filament\Resources\OrderOnlines\Pages\EditOrderOnline;
use App\Filament\Resources\OrderOnlines\Pages\ListOrderOnlines;
use App\Filament\Resources\OrderOnlines\Schemas\OrderOnlineForm;
use App\Filament\Resources\OrderOnlines\Tables\OrderOnlinesTable;
use App\Models\OrderOnline;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderOnlineResource extends Resource
{
    protected static ?string $model = OrderOnline::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OrderOnlineForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderOnlinesTable::configure($table);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Orders from website');
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
            'index' => ListOrderOnlines::route('/'),
            'create' => CreateOrderOnline::route('/create'),
            'edit' => EditOrderOnline::route('/{record}/edit'),
        ];
    }
}
