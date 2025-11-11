<?php

namespace App\Filament\Resources\OnlineOrderConfirmations\Pages;

use App\Filament\Resources\OnlineOrderConfirmations\OnlineOrderConfirmationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOnlineOrderConfirmations extends ListRecords
{
    protected static string $resource = OnlineOrderConfirmationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
