<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>غير مصرح بالدخول</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        .error-container {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .error-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .error-title {
            color: #e74c3c;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        .error-message {
            margin-bottom: 1rem;
            line-height: 1.6;
            font-size: 1.1rem;
        }
        .attempts-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            border-right: 4px solid #3498db;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
            font-weight: bold;
        }
        .btn:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">🚫</div>
        <h1 class="error-title">غير مصرح بالدخول</h1>

        <div class="error-message">
            <strong>ليس لديك صلاحية للوصول إلى هذا القسم.</strong><br>
            يرجى التواصل مع المسؤول لتفعيل الصلاحيات المناسبة.
        </div>

        <div class="attempts-info">
            <strong>معلومات المحاولات:</strong><br>
            المحاولات: {{ $attempts }} من 3<br>
            المحاولات المتبقية: {{ $remaining }}
        </div>

        <a href="{{ url('/') }}" class="btn">العودة للصفحة الرئيسية</a>
    </div>
</body>
</html>
