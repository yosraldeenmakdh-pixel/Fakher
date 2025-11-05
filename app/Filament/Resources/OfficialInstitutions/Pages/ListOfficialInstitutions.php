<?php

namespace App\Filament\Resources\OfficialInstitutions\Pages;

use App\Filament\Resources\OfficialInstitutions\OfficialInstitutionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOfficialInstitutions extends ListRecords
{
    protected static string $resource = OfficialInstitutionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
