<?php

namespace App\Filament\Resources\Offers\Pages;

use App\Filament\Resources\Offers\OfferResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOffer extends EditRecord
{
    protected static string $resource = OfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $this->syncRelationships();
    }

    private function syncRelationships(): void
    {
        $record = $this->record;
        $data = $this->form->getState();

        // تنظيف جميع العلاقات القديمة
        $record->categories()->detach();
        $record->meals()->detach();

        // ربط العلاقات حسب النوع المختار
        if (isset($data['link_type'])) {
            if ($data['link_type'] == 'category' && isset($data['category_id'])) {
                $record->categories()->attach($data['category_id']);
            } elseif ($data['link_type'] == 'meals' && isset($data['meal_ids'])) {
                $record->meals()->attach($data['meal_ids']);
            }
        }
    }

    // تحميل البيانات الحالية للعرض
    protected function fillForm(): void
    {
        $record = $this->record;

        $data = $record->toArray();

        // تحديد نوع الربط الحالي
        if ($record->categories()->count() > 0) {
            $data['link_type'] = 'category';
            $data['category_id'] = $record->categories()->first()->id;
        } elseif ($record->meals()->count() > 0) {
            $data['link_type'] = 'meals';
            $data['meal_ids'] = $record->meals()->pluck('id')->toArray();
        } else {
            $data['link_type'] = 'category';
        }

        $this->form->fill($data);
    }

}
