<?php

namespace App\Filament\Resources\BranchMeals\Pages;

use App\Filament\Resources\BranchMeals\BranchMealResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBranchMeals extends ListRecords
{
    protected static string $resource = BranchMealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
