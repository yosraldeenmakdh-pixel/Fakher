<?php

namespace App\Filament\Resources\OfficialInstitutions\Pages;

use App\Filament\Resources\OfficialInstitutions\OfficialInstitutionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOfficialInstitution extends EditRecord
{
    protected static string $resource = OfficialInstitutionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
