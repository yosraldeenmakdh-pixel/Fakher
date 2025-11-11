<?php

namespace App\Filament\Resources\OrderOnlines\Pages;

use App\Filament\Resources\OrderOnlines\OrderOnlineResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderOnline extends EditRecord
{
    protected static string $resource = OrderOnlineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
