<?php

namespace App\Filament\Resources\DailyKitchenSchedules;

use App\Filament\Resources\DailyKitchenSchedules\Pages\CreateDailyKitchenSchedule;
use App\Filament\Resources\DailyKitchenSchedules\Pages\EditDailyKitchenSchedule;
use App\Filament\Resources\DailyKitchenSchedules\Pages\ListDailyKitchenSchedules;
use App\Filament\Resources\DailyKitchenSchedules\Schemas\DailyKitchenScheduleForm;
use App\Filament\Resources\DailyKitchenSchedules\Tables\DailyKitchenSchedulesTable;
use App\Models\DailyKitchenSchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DailyKitchenScheduleResource extends Resource
{
    protected static ?string $model = DailyKitchenSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return DailyKitchenScheduleForm::configure($schema);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('kitchen');
    }

    public static function table(Table $table): Table
    {
        return DailyKitchenSchedulesTable::configure($table);
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
            'index' => ListDailyKitchenSchedules::route('/'),
            'create' => CreateDailyKitchenSchedule::route('/create'),
            'edit' => EditDailyKitchenSchedule::route('/{record}/edit'),
        ];
    }
}
