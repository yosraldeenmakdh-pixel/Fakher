<?php

namespace App\Filament\Resources\InstitutionOrders\Pages;

use App\Filament\Resources\InstitutionOrders\InstitutionOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstitutionOrders extends ListRecords
{
    protected static string $resource = InstitutionOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
