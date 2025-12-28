<?php

namespace App\Filament\Resources\BranchMeals\Pages;

use App\Filament\Resources\BranchMeals\BranchMealResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBranchMeal extends CreateRecord
{
    protected static string $resource = BranchMealResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $branchId = $data['branch_id'];
        $mealIds = $data['meal_ids'];
        $isAvailable = $data['is_available'];

        $lastRecord = null;

        foreach ($mealIds as $mealId) {
            // إنشاء سجل لكل وجبة تم اختيارها
            $lastRecord = static::getModel()::create([
                'branch_id' => $branchId,
                'meal_id' => $mealId,
                'is_available' => $isAvailable,
            ]);
        }

        return $lastRecord;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateAnotherFormAction()->hidden();
    }
}
