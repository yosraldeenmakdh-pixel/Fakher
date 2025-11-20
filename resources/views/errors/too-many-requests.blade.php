<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلبات كثيرة جداً</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #000000 0%, #000000 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #ffffff;
        }
        .error-container {
            background: rgb(24, 24, 24);
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
            color: #c23616;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        .error-message {
            margin-bottom: 2rem;
            line-height: 1.6;
            font-size: 1.1rem;
        }
        .timer {
            font-size: 2rem;
            font-weight: bold;
            color: #e74c3c;
            margin: 1rem 0;
        }
        .info-box {
            background: #fff9e6;
            color: #111111 ;
            padding: 1rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            border-right: 4px solid #f39c12;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⏰</div>
        <h1 class="error-title">طلبات كثيرة جداً</h1>

        <div class="error-message">
            <strong>لقد تجاوزت الحد المسموح به من المحاولات.</strong><br>
            تم حظر الوصول مؤقتاً لأسباب أمنية.
        </div>

        <div class="timer">
            ⏳ {{ $minutes }} دقيقة
        </div>



        {{-- <div style="margin-top: 1.5rem; font-size: 0.9rem; color: #7f8c8d;">
            للحماية من الهجمات الإلكترونية
        </div> --}}
    </div>

    <script>
        // عد تنازلي للوقت المتبقي
        let seconds = {{ $seconds }};
        const countdownElement = document.getElementById('countdown');

        const countdown = setInterval(() => {
            seconds--;
            countdownElement.textContent = seconds;

            if (seconds <= 0) {
                clearInterval(countdown);
                countdownElement.textContent = 'انتهى الوقت';
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        }, 1000);
    </script>
</body>
</html>
