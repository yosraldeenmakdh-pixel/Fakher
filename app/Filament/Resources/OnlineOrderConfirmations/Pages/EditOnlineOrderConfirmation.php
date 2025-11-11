<?php

namespace App\Filament\Resources\OnlineOrderConfirmations\Pages;

use App\Filament\Resources\OnlineOrderConfirmations\OnlineOrderConfirmationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOnlineOrderConfirmation extends EditRecord
{
    protected static string $resource = OnlineOrderConfirmationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
