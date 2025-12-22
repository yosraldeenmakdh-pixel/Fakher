<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف الحساب المالي - {{ $kitchen->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* إعدادات الطباعة */
        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            body {
                margin: 1.6cm;
            }
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
        }

        /* تنسيقات عامة */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', 'Arial', sans-serif;
            direction: rtl;
            text-align: right;
            line-height: 1.6;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }

        .report-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        /* رأس التقرير */
        .report-header {
            text-align: center;
            border-bottom: 3px solid #27ae60;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .report-title {
            color: #27ae60;
            font-size: 32px;
            margin: 0 0 10px 0;
        }

        .report-subtitle {
            font-size: 18px;
            color: #666;
        }

        /* معلومات المطبخ */
        .kitchen-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-right: 4px solid #27ae60;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: bold;
            min-width: 150px;
            color: #333;
        }

        .info-value {
            color: #555;
        }

        /* الإحصائيات */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 25px 0;
        }

        .stat-card {
            background: #e8f4ff;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #2c5aa0;
        }

        .stat-value {
            font-size: 24px;
            color: #2c5aa0;
            font-weight: bold;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
        }

        /* جدول الحركات */
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 14px;
        }

        .transactions-table th {
            background: #27ae60;
            color: white;
            padding: 12px 15px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #ddd;
        }

        .transactions-table td {
            padding: 10px 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .transactions-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        /* تذييل التقرير */
        .report-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 13px;
        }

        /* أزرار التحكم */
        .controls {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            font-family: 'Cairo', sans-serif;
        }

        .btn-print {
            background: #27ae60;
            color: white;
        }

        .btn-print:hover {
            background: #1e8449;
        }

        .btn-close {
            background: #dc3545;
            color: white;
        }

        .btn-close:hover {
            background: #c82333;
        }

        /* رسالة الطباعة */
        .print-message {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* في حالة الطباعة فقط */
        .print-only {
            display: none;
        }

        /* تنسيق الأرقام */
        .amount-positive {
            color: #27ae60;
            font-weight: bold;
        }

        .amount-negative {
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- رأس التقرير -->
        <div class="report-header">
            <h1 class="report-title">كشف الحساب المالي</h1>
            <div class="report-subtitle">
                <strong>المطبخ: </strong> {{ $kitchen->name }}
            </div>
            <div class="report-date">
                <strong>الفترة: </strong>
                @if($data['start_date'])
                    {{ \Carbon\Carbon::parse($data['start_date'])->format('d/m/Y') }}
                @else
                    البداية
                @endif
                -
                @if($data['end_date'])
                    {{ \Carbon\Carbon::parse($data['end_date'])->format('d/m/Y') }}
                @else
                    {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                @endif
            </div>
        </div>

        <!-- معلومات التقرير -->
        <div class="kitchen-info">
            <div class="info-row">
                <span class="info-label">نوع الحركات: </span>
                <span class="info-value">
                    @if($data['transaction_type'] == 'all')
                        جميع الحركات
                    @elseif($data['transaction_type'] == 'online_order')
                        طلبات إلكترونية
                    @else
                        دفعات
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">تاريخ الإنشاء: </span>
                <span class="info-value">{{ \Carbon\Carbon::now()->locale('ar')->translatedFormat('j/m/Y h:i A') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">الرصيد الحالي: </span>
                <span class="info-value" style="font-weight: bold; color: #2c5aa0;">
                    $ {{ number_format($kitchen->Financial_debts, 2) }}
                </span>
            </div>
        </div>

        <!-- إحصائيات -->
        {{-- <div class="stats-section">
            <div class="stat-card">
                <div class="stat-value">{{ $total_transactions }}</div>
                <div class="stat-label">إجمالي الحركات</div>
            </div>
            <div class="stat-card">
                <div class="stat-value amount-positive">$ {{ number_format($total_income, 2) }}</div>
                <div class="stat-label">إجمالي الإيرادات</div>
            </div>
            <div class="stat-card">
                <div class="stat-value amount-negative">$ {{ number_format($total_expenses, 2) }}</div>
                <div class="stat-label">إجمالي المصروفات</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    @if($net_flow >= 0)
                        <span class="amount-positive">+$ {{ number_format($net_flow, 2) }}</span>
                    @else
                        <span class="amount-negative">-$ {{ number_format(abs($net_flow), 2) }}</span>
                    @endif
                </div>
                <div class="stat-label">صافي التدفق</div>
            </div>
        </div> --}}

        <!-- تفصيل أنواع الحركات -->
        <h3>تفصيل أنواع الحركات</h3>
        <div class="stats-section">
            @foreach($transaction_types as $type => $count)
                @php
                    $percentage = $total_transactions > 0
                        ? round(($count / $total_transactions) * 100, 1)
                        : 0;
                    $typeName = $type == 'payment' ? 'دفعات' : ($type == 'online_order' ? 'طلبات إلكترونية' : $type);
                @endphp
                <div class="stat-card">
                    <div class="stat-value">{{ $count }}</div>
                    <div class="stat-label">{{ $typeName }}</div>
                    <div style="font-size: 12px; color: #999;">({{ $percentage }}%)</div>
                </div>
            @endforeach
        </div>

        <!-- جدول الحركات -->
        <h3>الحركات المالية ({{ $transactions->count() }} حركة)</h3>
        @if($transactions->count() > 0)
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th width="15%">التاريخ</th>
                        <th width="25%">نوع الحركة</th>
                        <th width="20%">المبلغ</th>
                        <th width="20%">الرصيد بعد الحركة</th>
                        <th width="20%">الوصف</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                        @php
                            $transactionDate = is_string($transaction->transaction_date)
                                ? \Carbon\Carbon::parse($transaction->transaction_date)
                                : $transaction->transaction_date;

                            $typeName = $transaction->transaction_type == 'payment'
                                ? 'دفعة'
                                : ($transaction->transaction_type == 'online_order'
                                    ? 'طلب إلكتروني'
                                    : $transaction->transaction_type);
                        @endphp
                        <tr>
                            <td>{{ $transactionDate->format('d/m/Y H:i') }}</td>
                            <td>{{ $typeName }}</td>
                            <td class="{{ $transaction->amount >= 0 ? 'amount-positive' : 'amount-negative' }}">
                                @if($transaction->amount >= 0)
                                    +$ {{ number_format($transaction->amount, 2) }}
                                @else
                                    -$ {{ number_format(abs($transaction->amount), 2) }}
                                @endif
                            </td>
                            <td>$ {{ number_format($transaction->balance_after, 2) }}</td>
                            <td>{{ $transaction->description ?? 'بدون وصف' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- ملخص الحركات -->
            <div class="kitchen-info" style="margin-top: 30px;">
                <h4>ملخص الحركات</h4>
                <div class="info-row">
                    <span class="info-label">أول حركة: </span>
                    <span class="info-value">{{ $transactions->last()->transaction_date->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">آخر حركة: </span>
                    <span class="info-value">{{ $transactions->first()->transaction_date->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        @else
            <div class="print-message">
                ⚠️ لا توجد حركات مالية في الفترة المحددة
            </div>
        @endif

        <!-- تذييل التقرير -->
        <div class="report-footer">
            <p>هذا التقرير تم إنشاؤه تلقائياً بواسطة النظام</p>
            <p class="print-only">تاريخ الطباعة: {{ \Carbon\Carbon::now()->locale('ar')->translatedFormat('j/m/Y h:i A') }}</p>
        </div>
    </div>

    <div class="controls no-print">
        <button class="btn btn-print" onclick="window.print()">
            🖨️ طباعة التقرير
        </button>
    </div>
</body>
</html>
