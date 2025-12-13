<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;

    protected static ?string $title = 'إضافة قطاع جديد';

    protected static ?string $breadcrumb = 'إضافة جديد';

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إضافة القطاع بنجاح';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('تمت الإضافة')
            ->body('تم إضافة القطاع بنجاح.')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->duration(3000);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('حفظ')
                ->icon('heroicon-o-check')
                ->color('primary'),

            $this->getCancelFormAction()
                ->label('إلغاء')
                ->icon('heroicon-o-x-mark')
                ->color('gray'),
        ];
    }
}
