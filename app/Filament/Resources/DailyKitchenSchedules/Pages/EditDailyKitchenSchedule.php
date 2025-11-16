<?php

namespace App\Filament\Resources\DailyKitchenSchedules\Pages;

use App\Filament\Resources\DailyKitchenSchedules\DailyKitchenScheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDailyKitchenSchedule extends EditRecord
{
    protected static string $resource = DailyKitchenScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
