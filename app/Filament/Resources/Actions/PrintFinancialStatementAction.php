<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Model;

class PrintFinancialStatementAction extends Action
{
    public static function make(string $name = null): static
    {
        return parent::make($name)
            ->label('كشف الحساب المالي')
            ->icon('heroicon-o-document-arrow-down')
            ->color('success')
            ->modalHeading(fn ($record) => "تصدير كشف الحساب المالي - {$record->name}")
            ->modalSubmitActionLabel('عرض التقرير')
            ->modalCancelActionLabel('إلغاء')

            ->form([
                Section::make('إعدادات التقرير')  // ✅ استخدم Section من Forms\Components
                    ->description('حدد الفترة والمعايير المطلوبة للتقرير')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('تاريخ البداية')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection(),

                        DatePicker::make('end_date')
                            ->label('تاريخ النهاية')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection()
                            ->default(now()->addDay(1)),

                        Select::make('transaction_type')
                            ->label('نوع الحركات')
                            ->options([
                                'all' => 'جميع الحركات',
                                'online_order' => 'طلبات إلكترونية',
                                'payment' => 'دفعات',
                            ])
                            ->default('all'),
                    ])
                    ->columns(2),
            ])
            ->action(function (array $data, Model $record) {
                // نوجه المستخدم إلى صفحة العرض مع تمرير المعطيات
                return redirect()->route('financial.statement.print', [
                    'kitchen' => $record->id,
                    'start_date' => $data['start_date'] ?? null,
                    'end_date' => $data['end_date'] ?? null,
                    'transaction_type' => $data['transaction_type'] ?? 'all',
                ]);
            });
            // ->openUrlInNewTab();
    }
}
