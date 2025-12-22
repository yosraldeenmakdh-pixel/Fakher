<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: 'Tajawal', 'Segoe UI', sans-serif; line-height: 1.6; color: #333; background-color: #f5f5f5; margin: 0; padding: 0; unicode-bidi: embed; height: 100vh; display: flex; align-items: center; justify-content: center;">
    <div style="max-width: 600px; width: 100%; padding: 20px;">
        <div style="background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="padding: 50px 30px; text-align: center;">
                <!-- الرسالة الترحيبية -->
                <p style="font-size: 18px; margin-bottom: 20px; color: #555; text-align: center;">
                    عزيزي المستخدم
                </p>

                <!-- نص التعليمات -->
                <p style="font-size: 16px; margin-bottom: 35px; color: #555; line-height: 1.8; text-align: center;">
                    لقد تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك<br>
                    يرجى النقر على الزر الموضح أدناه لإكمال عملية إعادة التعيين
                </p>

                <!-- الزر -->
                <div style="margin-top: 30px;">
                    @isset($actionText)
                    <?php
                        $color = match ($level ?? 'primary') {
                            'success' => '#28a745',
                            'error' => '#dc3545',
                            default => '#007bff',
                        };
                    ?>
                    <a href="{{ $actionUrl }}"
                       style="display: inline-block; padding: 14px 40px; background-color: {{ $color }}; color: white; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: bold; transition: all 0.3s ease;">
                        {{ $actionText }}
                    </a>
                    @endisset
                </div>

                <!-- نص احتياطي في حالة عدم وجود زر -->
                {{-- @unless(isset($actionText))
                <div style="margin-top: 20px; padding: 20px; background-color: #f8f9fa; border-radius: 8px; font-size: 14px; color: #666;">
                    <p>إذا كنت تواجه مشكلة في رؤية الزر، يرجى نسخ الرابط التالي والمتابعة:</p>
                    <p style="word-break: break-all; color: #007bff;">{{ $actionUrl ?? '#' }}</p>
                </div>
                @endunless --}}
            </div>
        </div>
    </div>
</body>
</html>
