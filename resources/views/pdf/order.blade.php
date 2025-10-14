<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>فاتورة الطلب #{{ $order->id }}</title>
    <style>
        /* تعريف الخط العربي */
        @font-face {
            font-family: 'Amiri';
            font-style: normal;
            font-weight: 400;
            src: url('file:///C:/php_Laravel/Fakher/storage/app/public/fonts/Amiri-Regular.ttf') format('truetype');
        }


        * {
            font-family: 'Amiri', 'DejaVu Sans', sans-serif;
        }

        body {
            direction: rtl;
            text-align: right;
            line-height: 1.8;
            color: #000;
            margin: 0;
            padding: 20px;
            font-size: 16px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .invoice-info {
            margin-bottom: 30px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }

        .invoice-info p {
            margin: 5px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .table th {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
            padding: 12px 8px;
            text-align: right;
            border: 1px solid #ddd;
        }

        .table td {
            padding: 10px 8px;
            border: 1px solid #ddd;
            text-align: right;
        }

        .table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .total-section {
            text-align: left;
            margin-top: 30px;
            padding: 15px;
            background: #e8f5e8;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            border: 1px solid #28a745;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 14px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 28px;
        }
    </style>
</head>
<body>
    <div class="logo">
        <h2>مطعمنا</h2>
    </div>

    <div class="header">
        <h1>فاتورة الطلب</h1>
        <p>رقم الفاتورة: #{{ $order->id }}</p>
        <p>تاريخ الطلب: {{ $order->created_at->translatedFormat('Y-m-d H:i') }}</p>
    </div>

    <div class="invoice-info">
        <p><strong>اسم العميل:</strong> {{ $order->name }}</p>
        <p><strong>الفرع:</strong> {{ $order->branch->name ?? 'غير محدد' }}</p>
        @if($order->special_instructions)
        <p><strong>تعليمات خاصة:</strong> {{ $order->special_instructions }}</p>
        @endif
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>اسم الوجبة</th>
                <th>الكمية</th>
                <th>سعر الوحدة</th>
                <th>المجموع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->meal->name ?? 'غير محدد' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->unit_price, 2) }} د.إ</td>
                <td>{{ number_format($item->total_price, 2) }} د.إ</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <p>المجموع الكلي: {{ number_format($order->total, 2) }} درهم إماراتي</p>
    </div>

    <div class="footer">
        <p>شكراً لثقتكم بنا</p>
        <p>للاستفسار يرجى التواصل مع خدمة العملاء</p>
        <p>هاتف: ٠١٢٣٤٥٦٧٨٩ | البريد الإلكتروني: info@restaurant.com</p>
    </div>
</body>
</html>
