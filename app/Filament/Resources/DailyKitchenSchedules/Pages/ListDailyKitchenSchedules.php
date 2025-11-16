<?php

namespace App\Filament\Resources\DailyKitchenSchedules\Pages;

use App\Filament\Resources\DailyKitchenSchedules\DailyKitchenScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDailyKitchenSchedules extends ListRecords
{
    protected static string $resource = DailyKitchenScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
