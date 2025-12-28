<?php

namespace App\Filament\Resources\BranchMeals\Pages;

use App\Filament\Resources\BranchMeals\BranchMealResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBranchMeal extends EditRecord
{
    protected static string $resource = BranchMealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
