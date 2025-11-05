<?php

namespace App\Filament\Resources\PublicRatings\Pages;

use App\Filament\Resources\PublicRatings\PublicRatingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPublicRating extends EditRecord
{
    protected static string $resource = PublicRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
