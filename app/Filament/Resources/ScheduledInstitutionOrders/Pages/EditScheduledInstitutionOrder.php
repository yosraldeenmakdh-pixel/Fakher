<?php

namespace App\Filament\Resources\ScheduledInstitutionOrders\Pages;

use App\Filament\Resources\ScheduledInstitutionOrders\ScheduledInstitutionOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScheduledInstitutionOrder extends EditRecord
{
    protected static string $resource = ScheduledInstitutionOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
