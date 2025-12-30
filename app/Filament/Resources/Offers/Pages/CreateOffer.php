<?php

namespace App\Filament\Resources\Offers\Pages;

use App\Filament\Resources\Offers\OfferResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOffer extends CreateRecord
{
    protected static string $resource = OfferResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
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
}
