<?php

namespace App\Filament\Resources\Emergencies\Pages;

use App\Filament\Resources\Emergencies\EmergencyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmergencies extends ListRecords
{
    protected static string $resource = EmergencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
