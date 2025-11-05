<?php

namespace App\Filament\Resources\InstitutionOrderConfirmations\Pages;

use App\Filament\Resources\InstitutionOrderConfirmations\InstitutionOrderConfirmationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInstitutionOrderConfirmation extends EditRecord
{
    protected static string $resource = InstitutionOrderConfirmationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
