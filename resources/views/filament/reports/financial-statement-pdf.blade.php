<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>كشف الحساب المالي - {{ $record->name }}</title>
    <style>
        /* إعدادات أساسية للغة العربية */
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path('fonts/dejavu-sans/DejaVuSans.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path('fonts/dejavu-sans/DejaVuSans-Bold.ttf') }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            line-height: 1.6;
            color: #000;
            background: #fff;
            padding: 20px;
            font-size: 12px;
            direction: rtl;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #2c5aa0;
            font-size: 20px;
            margin-bottom: 8px;
        }

        .header h2 {
            color: #555;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .header .period {
            color: #777;
            font-size: 12px;
        }

        .balance-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 12px;
            border-radius: 5px;
            text-align: center;
            margin: 15px 0;
        }

        .balance-card .label {
            font-size: 12px;
            color: #666;
        }

        .balance-card .amount {
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
            color: #2c5aa0;
        }

        .stats-grid {
            display: table;
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        .stat-card {
            display: table-cell;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
        }

        .stat-card .title {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-card .value {
            font-size: 12px;
            font-weight: bold;
        }

        .table-container {
            margin: 20px 0;
        }

        .table-title {
            background: #2c5aa0;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 10px;
        }

        th {
            background: #f8f9fa;
            color: #2c5aa0;
            padding: 8px 6px;
            text-align: right;
            border: 1px solid #dee2e6;
            font-weight: bold;
        }

        td {
            padding: 6px 4px;
            border: 1px solid #dee2e6;
            text-align: right;
        }

        tr:nth-child(even) {
            background: #f8f9fa;
        }

        .transaction-type {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .type-payment {
            background: #d1fae5;
            color: #065f46;
        }

        .type-scheduled {
            background: #dbeafe;
            color: #1e40af;
        }

        .type-special {
            background: #fef3c7;
            color: #92400e;
        }

        .type-emergency {
            background: #fee2e2;
            color: #991b1b;
        }

        .amount-positive {
            color: #059669;
            font-weight: bold;
        }

        .amount-negative {
            color: #dc2626;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: left;
            color: #666;
            font-size: 10px;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            color: #666;
            font-style: italic;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mt-2 {
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <!-- رأس التقرير -->
    <div class="header">
        <h1>كشف الحساب المالي</h1>
        <h2>{{ $record->name }}</h2>
        <p class="period">
            الفترة:
            {{ $filters['start_date'] ? \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') : 'البداية' }}
            -
            {{ \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y') }}
        </p>
    </div>

    <!-- الرصيد الحالي -->
    <div class="balance-card">
        <div class="label">الرصيد الحالي</div>
        <div class="amount">{{ number_format($statistics['current_balance'], 2) }} ر.س</div>
    </div>

    <!-- البطاقات الإحصائية -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="title">إجمالي الإيرادات</div>
            <div class="value amount-positive">{{ number_format($statistics['total_income'], 2) }} ر.س</div>
        </div>

        <div class="stat-card">
            <div class="title">إجمالي المصروفات</div>
            <div class="value amount-negative">{{ number_format($statistics['total_expenses'], 2) }} ر.س</div>
        </div>

        <div class="stat-card">
            <div class="title">صافي التدفق</div>
            <div class="value {{ $statistics['net_flow'] >= 0 ? 'amount-positive' : 'amount-negative' }}">
                {{ number_format($statistics['net_flow'], 2) }} ر.س
            </div>
        </div>

        <div class="stat-card">
            <div class="title">عدد الحركات</div>
            <div class="value">{{ $statistics['total_transactions'] }}</div>
        </div>
    </div>

    <!-- جدول الحركات المالية -->
    <div class="table-container">
        <div class="table-title">الحركات المالية</div>

        @if($transactions->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="15%">التاريخ</th>
                    <th width="15%">نوع الحركة</th>
                    <th width="40%">الوصف</th>
                    <th width="15%">المبلغ</th>
                    <th width="15%">الرصيد</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                @php
                    $transactionDate = is_string($transaction->transaction_date)
                        ? \Carbon\Carbon::parse($transaction->transaction_date)
                        : $transaction->transaction_date;
                @endphp
                <tr>
                    <td>{{ $transactionDate->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($transaction->transaction_type === 'payment')
                            <span class="transaction-type type-payment">دفعة</span>
                        @elseif($transaction->transaction_type === 'scheduled_order')
                            <span class="transaction-type type-scheduled">طلب مجدول</span>
                        @elseif($transaction->transaction_type === 'special_order')
                            <span class="transaction-type type-special">طلب خاص</span>
                        @elseif($transaction->transaction_type === 'emergency_order')
                            <span class="transaction-type type-emergency">طلب استنفار</span>
                        @else
                            <span class="transaction-type">{{ $transaction->transaction_type }}</span>
                        @endif
                    </td>
                    <td>{{ $transaction->description ?? 'بدون وصف' }}</td>
                    <td class="{{ $transaction->amount >= 0 ? 'amount-positive' : 'amount-negative' }}">
                        {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }} ر.س
                    </td>
                    <td>{{ number_format($transaction->balance_after, 2) }} ر.س</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="no-data">
            <p>لا توجد حركات مالية في الفترة المحددة</p>
        </div>
        @endif
    </div>

    <!-- تذييل التقرير -->
    <div class="footer">
        <p>تم إنشاء التقرير في: {{ now()->format('d/m/Y H:i') }}</p>
        <p>نظام إدارة المؤسسات</p>
    </div>
</body>
</html>
