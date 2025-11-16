<?php

namespace App\Filament\Resources\ScheduledInstitutionOrders\Pages;

use App\Filament\Resources\ScheduledInstitutionOrders\ScheduledInstitutionOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScheduledInstitutionOrders extends ListRecords
{
    protected static string $resource = ScheduledInstitutionOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
