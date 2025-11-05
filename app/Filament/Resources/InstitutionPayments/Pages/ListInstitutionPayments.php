<?php

namespace App\Filament\Resources\InstitutionPayments\Pages;

use App\Filament\Resources\InstitutionPayments\InstitutionPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstitutionPayments extends ListRecords
{
    protected static string $resource = InstitutionPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
