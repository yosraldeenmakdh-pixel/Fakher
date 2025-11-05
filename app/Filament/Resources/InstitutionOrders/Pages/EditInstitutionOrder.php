<?php

namespace App\Filament\Resources\InstitutionOrders\Pages;

use App\Filament\Resources\InstitutionOrders\InstitutionOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInstitutionOrder extends EditRecord
{
    protected static string $resource = InstitutionOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
