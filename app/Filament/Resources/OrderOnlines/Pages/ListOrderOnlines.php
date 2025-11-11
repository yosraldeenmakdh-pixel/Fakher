<?php

namespace App\Filament\Resources\OrderOnlines\Pages;

use App\Filament\Resources\OrderOnlines\OrderOnlineResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrderOnlines extends ListRecords
{
    protected static string $resource = OrderOnlineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
