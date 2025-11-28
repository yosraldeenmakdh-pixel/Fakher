<?php

namespace App\Filament\Resources\Emergencies\Pages;

use App\Filament\Resources\Emergencies\EmergencyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmergency extends EditRecord
{
    protected static string $resource = EmergencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
