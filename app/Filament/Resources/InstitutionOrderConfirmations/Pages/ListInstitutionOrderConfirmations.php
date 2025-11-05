<?php

namespace App\Filament\Resources\InstitutionOrderConfirmations\Pages;

use App\Filament\Resources\InstitutionOrderConfirmations\InstitutionOrderConfirmationResource;
use App\Filament\Widgets\OrderStats;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstitutionOrderConfirmations extends ListRecords
{
    protected static string $resource = InstitutionOrderConfirmationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

}
