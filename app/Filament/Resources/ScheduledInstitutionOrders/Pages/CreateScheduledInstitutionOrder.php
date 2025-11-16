<?php

namespace App\Filament\Resources\ScheduledInstitutionOrders\Pages;

use App\Filament\Resources\ScheduledInstitutionOrders\ScheduledInstitutionOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateScheduledInstitutionOrder extends CreateRecord
{
    protected static string $resource = ScheduledInstitutionOrderResource::class;
}
