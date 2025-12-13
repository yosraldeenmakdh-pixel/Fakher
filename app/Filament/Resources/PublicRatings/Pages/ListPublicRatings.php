<?php

namespace App\Filament\Resources\PublicRatings\Pages;

use App\Filament\Resources\PublicRatings\PublicRatingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPublicRatings extends ListRecords
{
    protected static string $resource = PublicRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('إضافة تقييم عام جديد'),
        ];
    }
}
