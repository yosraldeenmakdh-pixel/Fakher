<?php

namespace App\Filament\Resources\OfficialInstitutions\Tables;

use App\Models\InstitutionFinancialTransaction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;

class OfficialInstitutionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->hasRole('institution')) {
                    return $query->where('user_id', Auth::id());
                }
                return $query;
            })
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المؤسسة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contract_number')
                    ->label('رقم العقد')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('institution_type')
                    ->label('نوع المؤسسة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'جهة حكومية',
                        'normal' => 'مؤسسة خاصة',
                    }),

                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kitchen.name')
                    ->label('المطبخ')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contract_start_date')
                    ->label('بداية العقد')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('contract_end_date')
                    ->label('نهاية العقد')
                    ->date('Y-m-d')
                    ->sortable()
                    ->color(function ($record) {
                        // if ($record->contract_end_date->isPast()) {
                        //     return 'danger';
                        // }
                        // if ($record->contract_end_date->diffInDays(now()) < 30) {
                        //     return 'warning';
                        // }
                        return 'success';
                    }),

                TextColumn::make('contract_status')
                    ->label('حالة العقد')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'suspended' => 'warning',
                        'renewed' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'suspended' => 'موقوف',
                        'renewed' => 'مجدد',
                    }),

                TextColumn::make('Financial_debts')
                    ->label('الرصيد')
                    ->sortable()
                    ->color(fn ($record) => $record->Financial_debts < 0 ? 'danger' : 'success')
                    ->size('lg')
                    ->formatStateUsing(function ($state, $record) {
                        $icon = $state < 0 ? 'heroicon-o-arrow-trending-down' : 'heroicon-o-arrow-trending-up';
                        $color = $state < 0 ? 'danger' : 'success';
                        $formatted = number_format($state, 2);

                        return "
                            <div class='flex items-center gap-2 rtl:flex-row-reverse'>
                                <x-heroicon-o-arrow-trending-up class='w-5 h-5 text-{$color}-500' />
                                <span class='font-bold text-{$color}-600 text-lg'>{$formatted}</span>
                            </div>
                        ";
                    })
                    ->html(),

                TextColumn::make('contact_phone')
                    ->label('هاتف التواصل')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('contact_email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ...(Auth::user()->hasRole('institution') ? [] : [
                    TextColumn::make('created_at')
                        ->label('تاريخ الإنشاء')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),

                    TextColumn::make('updated_at')
                        ->label('تاريخ التحديث')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ]),
            ])
            ->filters([
                ...(Auth::user()->hasRole('institution') ? [] : [
                    SelectFilter::make('contract_status')
                        ->label('حالة العقد')
                        ->options([
                            'active' => 'نشط',
                            'expired' => 'منتهي',
                            'suspended' => 'موقوف',
                            'renewed' => 'مجدد',
                        ]),

                    Filter::make('contract_expiring_soon')
                        ->label('العقود المنتهية قريباً')
                        ->query(fn (Builder $query): Builder => $query->where('contract_end_date', '<=', now()->addDays(30))),

                    Filter::make('contract_expired')
                        ->label('العقود المنتهية')
                        ->query(fn (Builder $query): Builder => $query->where('contract_end_date', '<', now())),

                    Filter::make('has_financial_debts')
                        ->label('لديه ديون مالية')
                        ->query(fn (Builder $query): Builder => $query->where('Financial_debts', '<', 0)),
                ]),
            ])
            ->recordActions([
                ActionGroup::make([
                EditAction::make(),
                DeleteAction::make(),

                Action::make('financialStatement')
                    ->label('تصدير كشف الحساب')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->modalHeading(fn ($record) => "تصدير كشف الحساب المالي - {$record->name}")
                    ->modalSubmitActionLabel('تصدير التقرير')
                    ->modalCancelActionLabel('إلغاء')
                    ->form([
                        Section::make('إعدادات التقرير')
                            ->description('حدد الفترة والمعايير المطلوبة للتقرير')
                            ->schema([
                                \Filament\Forms\Components\DatePicker::make('start_date')
                                    ->label('تاريخ البداية')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->closeOnDateSelection(),

                                \Filament\Forms\Components\DatePicker::make('end_date')
                                    ->label('تاريخ النهاية')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->closeOnDateSelection()
                                    ->default(now()->addDay(1)),

                                \Filament\Forms\Components\Select::make('transaction_type')
                                    ->label('نوع الحركات')
                                    ->options([
                                        'all' => 'جميع الحركات',
                                        'scheduled_order' => 'طلبات مجدولة',
                                        'special_order' => 'طلبات خاصة',
                                        'emergency_order' => 'طلبات استنفار',
                                        'payment' => 'دفعات',
                                    ])
                                    ->default('all'),
                            ])
                            ->columns(2),
                    ])
                    ->action(function (array $data, $record) {
                        return self::exportFinancialReport($data, $record);
                    }),
                ]),
            ]);

    }

    private static function exportFinancialReport(array $data, $record)
    {
        try {
            $startDate = $data['start_date'] ?? null;
            $endDate = $data['end_date'] ?? now();
            $transactionType = $data['transaction_type'] ?? 'all';

            $query = InstitutionFinancialTransaction::where('institution_id', $record->id)
                ->where('status', 'completed')
                ->when($startDate, fn($q) => $q->where('transaction_date', '>=', $startDate))
                ->when($endDate, fn($q) => $q->where('transaction_date', '<=', $endDate))
                ->when($transactionType !== 'all', fn($q) => $q->where('transaction_type', $transactionType))
                ->orderBy('transaction_date', 'desc');

            $transactions = $query->get();
            $currentBalance = $record->Financial_debts;

            $statistics = self::calculateFinancialStatistics($transactions, $currentBalance);

            return self::exportToText($record, $transactions, $statistics, $data);

        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('خطأ في تصدير التقرير')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }

    private static function exportToText($record, $transactions, $statistics, $data)
    {
        $fileName = 'كشف-الحساب-' . $record->name . '-' . now()->format('Y-m-d') . '.txt';

        return response()->streamDownload(function () use ($record, $transactions, $statistics, $data, $fileName) {
            // إضافة BOM للتعريف بالترميز UTF-8 والاتجاه RTL
            echo "\xEF\xBB\xBF";

            $output = "";

            // ==================== رأس التقرير ====================
            $output .= self::createHeader("كشف الحساب المالي");
            $output .= self::formatLineRTL("المؤسسة:", $record->name);

            $startDateFormatted = isset($data['start_date']) && !empty($data['start_date'])
                ? Date::parse($data['start_date'])->format('d/m/Y')
                : 'البداية';

            $endDateFormatted = isset($data['end_date']) && !empty($data['end_date'])
                ? Date::parse($data['end_date'])->format('d/m/Y')
                : Date::now()->format('d/m/Y');

            $output .= self::formatLineRTL("الفترة:", $startDateFormatted . " - " . $endDateFormatted);
            $output .= self::createSeparator();


            $output .= self::formatLineRTL("عدد الحركات:", $statistics['total_transactions'] . " | 📊 إجمالي");
            $output .= "\n";

            // ==================== تفصيل أنواع الحركات ====================
            $output .= self::createSectionHeaderRTL("تفصيل أنواع الحركات");

            foreach ($statistics['transaction_types'] as $type => $count) {
                $typeName = self::getTransactionTypeArabic($type);
                $percentage = $statistics['total_transactions'] > 0
                    ? round(($count / $statistics['total_transactions']) * 100, 1)
                    : 0;
                $output .= self::formatLineRTL($typeName . ":", $count . " حركة (" . $percentage . "%)");
            }
            $output .= "\n";

            // ==================== الحركات المالية ====================
            if ($transactions->count() > 0) {
                $output .= self::createSectionHeaderRTL("الحركات المالية (" . $transactions->count() . " حركة)");

                foreach ($transactions as $transaction) {
                    $transactionDate = is_string($transaction->transaction_date)
                        ? Date::parse($transaction->transaction_date)
                        : $transaction->transaction_date;


                    $amount = ($transaction->amount >= 0 ? "+$ " : "-$ ") . number_format(abs($transaction->amount), 2);
                    $balance = "$ " . number_format($transaction->balance_after, 2);

                    $currentTypeName = self::getTransactionTypeArabic($transaction->transaction_type);

                    // ترتيب أعمدة RTL
                    $output .= self::createTableRowRTL([
                        $balance,
                        $amount,
                        $currentTypeName,
                        $transactionDate->format('d/m/Y H:i')
                    ], [15, 15, 25, 15, 18]);
                }

                $output .= self::createSeparator();

                // ملخص الحركات
                $output .= self::createSectionHeaderRTL("ملخص الحركات");
                $output .= self::formatLineRTL("عدد الحركات:", $transactions->count() . " حركة");

                if ($transactions->count() > 0) {
                    $output .= self::formatLineRTL("أول حركة:", $transactions->last()->transaction_date->format('d/m/Y H:i'));
                    $output .= self::formatLineRTL("آخر حركة:", $transactions->first()->transaction_date->format('d/m/Y H:i'));
                }

            } else {
                $output .= self::createSectionHeaderRTL("الحركات المالية");
                $output .= "⚠️   لا توجد حركات مالية في الفترة المحددة\n";
            }

            // ==================== تذييل التقرير ====================
            $output .= "\n" . self::createSeparator();
            $output .= self::createSectionHeaderRTL("معلومات النظام");
            $output .= self::formatLineRTL("تم الإنشاء:", Date::now()->format('d/m/Y H:i'));
            echo $output;
        }, $fileName, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }

    // ==================== دوال مساعدة للتنسيق RTL ====================

    private static function createHeader($title)
    {
        $output = str_repeat("=", 60) . "\n";
        $output .= str_pad($title, 50, " ", STR_PAD_BOTH) . "\n";
        $output .= str_repeat("=", 60) . "\n";
        return $output;
    }

    private static function createSectionHeaderRTL($title)
    {
        return $title . ":\n" . str_repeat("-", 30) . "\n";
    }

    private static function createSeparator()
    {
        return str_repeat("-", 60) . "\n";
    }

    private static function formatLineRTL($label, $value, $totalWidth = 50)
    {
        // للغة العربية: القيمة أولاً ثم التسمية
        $padding = $totalWidth - mb_strlen($label) - mb_strlen($value);
        if ($padding < 1) {
            $padding = 1;
        }

        return $value . str_repeat(" ", $padding) . $label . "\n";
    }

    private static function createTableHeaderRTL($columns)
    {
        $output = "";
        $totalWidth = array_sum($columns) + (count($columns) * 3) - 1;

        // رأس الجدول - RTL: نبدأ من اليمين
        $headerLine = "";
        foreach ($columns as $title => $width) {
            $headerLine = str_pad($title, $width) . " | " . $headerLine;
        }
        $headerLine = rtrim($headerLine, " | ") . "\n";

        $output .= $headerLine;
        $output .= str_repeat("-", $totalWidth) . "\n";

        return $output;
    }

    private static function createTableRowRTL($data, $widths)
    {
        $row = "";
        // RTL: نبدأ من آخر عنصر (اليمين) إلى أول عنصر (اليسار)
        for ($i = count($data) - 1; $i >= 0; $i--) {
            $width = $widths[$i] ?? 15;
            $row .= str_pad($data[$i], $width) . " | ";
        }
        $row = rtrim($row, " | ") . "\n";
        return $row;
    }

    private static function getTransactionTypeArabic($type)
    {
        $types = [
            'payment' => 'دفعة',
            'scheduled_order' => 'مجدول',
            'special_order' => 'خاص',
            'emergency_order' => 'استنفار',
        ];

        return $types[$type] ?? $type;
    }

    private static function calculateFinancialStatistics($transactions, $currentBalance)
    {
        return [
            'total_transactions' => $transactions->count(),
            'total_income' => $transactions->where('amount', '>', 0)->sum('amount'),
            'total_expenses' => abs($transactions->where('amount', '<', 0)->sum('amount')),
            'net_flow' => $transactions->sum('amount'),
            'current_balance' => $currentBalance,
            'transaction_types' => $transactions->groupBy('transaction_type')->map->count(),
        ];
    }
}
