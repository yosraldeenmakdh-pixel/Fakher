<?php

namespace App\Filament\Resources\Kitchens\Tables;

use App\Models\KitchenFinancialTransaction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

class KitchensTable
{
    public static function configure(Table $table): Table
    {

        $user = Auth::user() ;

        return $table

            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->hasRole('kitchen')) {
                    return $query->where('user_id', Auth::id());
                }

                return $query;
            })
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المطبخ')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('المسؤول')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contact_phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->icon('heroicon-o-phone'),
                TextColumn::make('address')
                    ->label('العنوان')
                    ->searchable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('opening_time')
                    ->label('وقت الفتح')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('closing_time')
                    ->label('وقت الإغلاق')
                    ->time('H:i')
                    ->sortable(),



                TextColumn::make('Financial_debts')
                    ->label('الرصيد')
                    ->sortable()
                    ->color(fn ($record) => $record->Financial_debts < 0 ? 'danger' : 'success')
                    // ->weight('bold')s
                    ->size('lg')
                    ->formatStateUsing(function ($state, $record) {
                        $icon = $state < 0 ? 'heroicon-o-arrow-trending-down' : 'heroicon-o-arrow-trending-up';
                        $color = $state < 0 ? 'danger' : 'success';
                        $formatted = number_format($state, 2) ;

                        return "
                            <div class='flex items-center gap-2 rtl:flex-row-reverse'>
                                <x-heroicon-o-arrow-trending-up class='w-5 h-5 text-{$color}-500' />
                                <span class='font-bold text-{$color}-600 text-lg'>{$formatted}</span>
                            </div>
                        ";
                    })
                    ->html() ,

                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(!$user->hasRole('kitchen')) ,
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(!$user->hasRole('kitchen')) ,
            ])
            ->recordActions([
                ActionGroup::make([
                EditAction::make(),
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
                                        'online_order' => 'طلبات إلكترونية',
                                        'order' => 'طلبات داخلية',
                                        'payment' => 'دفعات',
                                    ])
                                    ->default('all'),
                            ])
                            ->columns(2),
                    ])
                    ->action(function (array $data, $record) {
                        return self::exportFinancialReport($data, $record);
                    }),
                ])
            ]);
    }

    private static function exportFinancialReport(array $data, $record)
    {
        try {
            $startDate = $data['start_date'] ?? null;
            $endDate = $data['end_date'] ?? now();
            $transactionType = $data['transaction_type'] ?? 'all';

            $query = KitchenFinancialTransaction::where('kitchen_id', $record->id)
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
        $fileName = 'كشف-حساب-مطبخ-' . $record->name . '-' . now()->format('Y-m-d') . '.txt';

        return response()->streamDownload(function () use ($record, $transactions, $statistics, $data, $fileName) {
            // إضافة BOM للتعريف بالترميز UTF-8 والاتجاه RTL
            echo "\xEF\xBB\xBF";

            $output = "";

            // ==================== رأس التقرير ====================
            $output .= self::createHeader("كشف الحساب المالي - المطبخ");
            $output .= self::formatLineRTL("المطبخ:", $record->name);

            $startDateFormatted = isset($data['start_date']) && !empty($data['start_date'])
                ? Date::parse($data['start_date'])->format('d/m/Y')
                : 'البداية';

            $endDateFormatted = isset($data['end_date']) && !empty($data['end_date'])
                ? Date::parse($data['end_date'])->format('d/m/Y')
                : Date::now()->format('d/m/Y');

            $output .= self::formatLineRTL("الفترة:", $startDateFormatted . " - " . $endDateFormatted);
            $output .= self::createSeparator();

            // ==================== الإحصائيات المالية ====================
            // $output .= self::createSectionHeaderRTL("الإحصائيات المالية");

            // $balanceStatus = $statistics['current_balance'] >= 0 ? "🟢 موجب" : "🔴 سالب";
            // $output .= self::formatLineRTL("الرصيد الحالي:", "$ " . number_format($statistics['current_balance'], 2) . " | " . $balanceStatus);
            // $output .= self::formatLineRTL("إجمالي الإيرادات:", "$ " . number_format($statistics['total_income'], 2) . " | 🟢 دخل");
            // $output .= self::formatLineRTL("إجمالي المصروفات:", "$ " . number_format($statistics['total_expenses'], 2) . " | 🔴 صرف");

            // $netFlowSign = $statistics['net_flow'] >= 0 ? "+" : "";
            // $flowStatus = $statistics['net_flow'] >= 0 ? "🟢 صافي موجب" : "🔴 صافي سالب";
            // $output .= self::formatLineRTL("صافي التدفق:", $netFlowSign . "$ " . number_format($statistics['net_flow'], 2) . " | " . $flowStatus);

            $output .= self::formatLineRTL("عدد الحركات:", $statistics['total_transactions'] . " | 📊 إجمالي");
            $output .= "\n";

            // ==================== تفصيل أنواع الحركات ====================
            $output .= self::createSectionHeaderRTL("تفصيل أنواع الحركات");

            foreach ($statistics['transaction_types'] as $type => $count) {
                $typeName = self::getKitchenTransactionTypeArabic($type);
                $percentage = $statistics['total_transactions'] > 0
                    ? round(($count / $statistics['total_transactions']) * 100, 1)
                    : 0;
                $output .= self::formatLineRTL($typeName . ":", $count . " حركة (" . $percentage . "%)");
            }
            $output .= "\n";

            // ==================== الحركات المالية ====================
            if ($transactions->count() > 0) {
                $output .= self::createSectionHeaderRTL("الحركات المالية (" . $transactions->count() . " حركة)");

                // رأس الجدول - ترتيب أعمدة RTL
                // $output .= self::createTableHeaderRTL([
                //     'الرصيد' => 15,
                //     'المبلغ' => 15,
                //     'الوصف' => 25,
                //     'نوع الحركة' => 15,
                //     'التاريخ' => 18
                // ]);

                foreach ($transactions as $transaction) {
                    $transactionDate = is_string($transaction->transaction_date)
                        ? Date::parse($transaction->transaction_date)
                        : $transaction->transaction_date;

                    // $typeName = self::getKitchenTransactionTypeArabic($transaction->transaction_type);
                    // $description = $transaction->description ?? 'بدون وصف';

                    // // تقصير الوصف إذا كان طويلاً
                    // if (mb_strlen($description) > 23) {
                    //     $description = mb_substr($description, 0, 20) . '...';
                    // }

                    $amount = ($transaction->amount >= 0 ? "+$ " : "-$ ") . number_format(abs($transaction->amount), 2);
                    $balance = "$ " . number_format($transaction->balance_after, 2);

                    $currentTypeName = self::getKitchenTransactionTypeArabic($transaction->transaction_type);


                    // ترتيب أعمدة RTL
                    $output .= self::createTableRowRTL([
                        $balance,
                        $amount,
                        // $description,
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
            // $output .= self::formatLineRTL("اسم الملف:", $fileName);
            // $output .= self::formatLineRTL("نوع التقرير:", "كشف الحساب المالي - المطبخ");
            // $output .= self::createSeparator();
            // $output .= "نظام إدارة المطابخ - © " . Date::now()->format('Y') . "\n";

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

    private static function getKitchenTransactionTypeArabic($type)
    {
        $types = [
            'payment' => 'دفعة',
            'scheduled_order' => 'طلب مجدول',
            'special_order' => 'طلب خاص',
            'emergency_order' => 'طلب استنفار',
            'online_order' => 'طلب إلكتروني',
            'order' => 'طلب داخلي',
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
