<?php

namespace App\Filament\Resources\OnlineOrderConfirmations;

use App\Filament\Resources\OnlineOrderConfirmations\Pages\CreateOnlineOrderConfirmation;
use App\Filament\Resources\OnlineOrderConfirmations\Pages\EditOnlineOrderConfirmation;
use App\Filament\Resources\OnlineOrderConfirmations\Pages\ListOnlineOrderConfirmations;
use App\Filament\Resources\OnlineOrderConfirmations\Schemas\OnlineOrderConfirmationForm;
use App\Filament\Resources\OnlineOrderConfirmations\Tables\OnlineOrderConfirmationsTable;
use App\Models\OnlineOrderConfirmation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OnlineOrderConfirmationResource extends Resource
{
    protected static ?string $model = OnlineOrderConfirmation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OnlineOrderConfirmationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OnlineOrderConfirmationsTable::configure($table);
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
            'index' => ListOnlineOrderConfirmations::route('/'),
            'create' => CreateOnlineOrderConfirmation::route('/create'),
            'edit' => EditOnlineOrderConfirmation::route('/{record}/edit'),
        ];
    }
}
