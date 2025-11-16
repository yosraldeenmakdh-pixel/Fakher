<?php

namespace App\Filament\Resources\InstitutionalMealPrices\Pages;

use App\Filament\Resources\InstitutionalMealPrices\InstitutionalMealPriceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstitutionalMealPrices extends ListRecords
{
    protected static string $resource = InstitutionalMealPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
