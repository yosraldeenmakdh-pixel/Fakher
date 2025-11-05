<?php

namespace App\Filament\Resources\InstitutionPayments\Pages;

use App\Filament\Resources\InstitutionPayments\InstitutionPaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInstitutionPayment extends EditRecord
{
    protected static string $resource = InstitutionPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }
}
