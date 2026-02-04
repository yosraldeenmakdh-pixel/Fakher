<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف حساب - {{ $kitchen->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* إعدادات الطباعة الذكية */
        @media print {
            @page {
                size: A4;
                margin: 1cm; /* تقليل الهوامش لاستغلال المساحة */
            }
            body {
                margin: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print { display: none !important; }
            .report-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            tr { page-break-inside: avoid; } /* منع انقسام الصف بين صفحتين */
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Cairo', sans-serif;
            direction: rtl;
            line-height: 1.4;
            color: #333;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
            font-size: 13px; /* خط أصغر قليلاً للطباعة المكثفة */
        }

        .report-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 15px;
            background: white;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        /* الرأس الرشيق */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #27ae60;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .report-title-area h1 {
            color: #27ae60;
            font-size: 22px;
            margin: 0;
        }

        .report-meta {
            text-align: left;
            font-size: 11px;
            color: #666;
        }

        /* تخطيط ذكي للمعلومات (عرضي بدلاً من طولي) */
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .info-card {
            background: #f8f9fa;
            padding: 10px;
            border-right: 3px solid #27ae60;
            border-radius: 4px;
        }

        .info-item {
            display: flex;
            margin-bottom: 4px;
            border-bottom: 1px dashed #eee;
            padding-bottom: 2px;
        }

        .info-label { font-weight: bold; width: 110px; color: #555; }

        /* الإحصائيات المختصرة */
        .stats-inline {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 8px;
        }

        .stat-box {
            background: #eef7f1;
            padding: 8px;
            text-align: center;
            border-radius: 4px;
            border: 1px solid #c8e6c9;
        }

        .stat-box .val {
            display: block;
            font-weight: 700;
            color: #27ae60;
            font-size: 16px;
        }
        .stat-box .lbl { font-size: 10px; color: #666; }

        /* الجدول المكثف */
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .transactions-table th {
            background: #27ae60;
            color: white;
            padding: 6px 8px;
            font-size: 12px;
            border: 1px solid #219150;
        }

        .transactions-table td {
            padding: 5px 8px;
            border: 1px solid #eee;
            text-align: center;
            font-size: 11.5px;
        }

        .transactions-table tr:nth-child(even) { background: #fafafa; }

        /* تنسيقات المبالغ */
        .amt-pos { color: #27ae60; font-weight: bold; }
        .amt-neg { color: #e74c3c; font-weight: bold; }

        .footer-note {
            margin-top: 15px;
            font-size: 10px;
            color: #999;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }

        .controls { text-align: center; margin-top: 20px; }
        .btn {
            padding: 8px 25px;
            font-family: 'Cairo';
            cursor: pointer;
            border: none;
            border-radius: 4px;
            font-weight: 600;
        }
        .btn-print { background: #27ae60; color: white; }
    </style>
</head>
<body>

    <div class="report-container">
        <header class="report-header">
            <div class="report-title-area">
                <h1>كشف حساب مالي</h1>
                <span style="font-size: 14px;">{{ $kitchen->name }}</span>
            </div>
            <div class="report-meta">
                <div>تاريخ التقرير: {{ \Carbon\Carbon::now()->translatedFormat('d/m/Y') }}</div>
                <div>الفترة: {{ $data['start_date'] ? \Carbon\Carbon::parse($data['start_date'])->format('d/m/Y') : 'البداية' }} - {{ $data['end_date'] ? \Carbon\Carbon::parse($data['end_date'])->format('d/m/Y') : 'الآن' }}</div>
            </div>
        </header>

        <div class="summary-grid">
            <div class="info-card">
                <div class="info-item">
                    <span class="info-label">نوع الحركة:</span>
                    <span>{{ $data['transaction_type'] == 'all' ? 'الكل' : ($data['transaction_type'] == 'online_order' ? 'طلبات' : 'دفعات') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">الرصيد الحالي:</span>
                    <span class="amt-pos" style="color:#2c5aa0">
                        {{ number_format(abs($kitchen->Financial_debts), 2) }}
                        {{ $kitchen->Financial_debts < 0 ? '-' : '' }} ل.س
                    </span>
                </div>
            </div>

            <div class="stats-inline">
                @foreach($transaction_types as $type => $count)
                    <div class="stat-box">
                        <span class="val">{{ $count }}</span>
                        <span class="lbl">{{ $type == 'payment' ? 'دفعات' : 'طلبات' }}</span>
                    </div>
                @endforeach
                <div class="stat-box" style="background: #e3f2fd; border-color: #bbdefb;">
                    <span class="val">{{ $transactions->count() }}</span>
                    <span class="lbl">إجمالي الحركات</span>
                </div>
            </div>
        </div>

        <table class="transactions-table">
            <thead>
                <tr>
                    <th width="15%">التاريخ</th>
                    <th width="15%">النوع</th>
                    <th width="18%">الرصيد السابق</th>
                    <th width="14%">المبلغ</th>
                    <th width="18%">الرصيد الحالي</th>
                    <th>الوصف</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i') }}</td>
                    <td>{{ $transaction->transaction_type == 'payment' ? 'دفعة' : 'طلب إلكتروني' }}</td>
                    <td>{{ number_format(abs($transaction->balance_before), 2) }}{{ $transaction->balance_before < 0 ? '-' : '' }}</td>
                    <td class="{{ $transaction->amount >= 0 ? 'amt-pos' : 'amt-neg' }}">
                        {{ number_format(abs($transaction->amount), 2) }}{{ $transaction->amount < 0 ? '-' : '' }}
                    </td>
                    <td>{{ number_format(abs($transaction->balance_after), 2) }}{{ $transaction->balance_after < 0 ? '-' : '' }}</td>
                    <td style="text-align: right; font-size: 10px;">{{ $transaction->description ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <footer class="footer-note">
            <p>تم استخراج هذا الكشف آلياً - نظام الإدارة المالية | تاريخ الطباعة: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
        </footer>
    </div>

    <div class="controls no-print">
        <button class="btn btn-print" onclick="window.print()">🖨️ طباعة كشف الحساب</button>
    </div>

</body>
</html>
