<?php

namespace App\Filament\Resources\InstitutionalMealPrices\Pages;

use App\Filament\Resources\InstitutionalMealPrices\InstitutionalMealPriceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInstitutionalMealPrice extends EditRecord
{
    protected static string $resource = InstitutionalMealPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
