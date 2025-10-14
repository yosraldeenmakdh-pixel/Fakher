<?php

namespace App\Filament\Resources\Actions ;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintOrderAction extends Action
{
    public static function make(string $name = null): static
    {
        return parent::make($name)
            ->label('تحميل الفاتورة')
            ->icon('heroicon-o-printer')
            ->color('success')
            ->action(function (Model $record) {
                // إنشاء PDF
                $pdf = Pdf::loadView('pdf.order', ['order' => $record]);

                // تحميل الملف
                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    "فاتورة-طلب-{$record->id}.pdf"
                );
            });
    }
}
