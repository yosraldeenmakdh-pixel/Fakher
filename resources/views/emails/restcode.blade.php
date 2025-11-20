<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة المرور</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap');
    </style>
</head>
<body style="font-family: 'Tajawal', 'Segoe UI', sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;">
    <div style="background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #d32f2f, #f44336); color: white; padding: 30px; text-align: center;">
            <h1 style="margin: 0; font-size: 26px; font-weight: 700;">طلب إعادة تعيين كلمة المرور</h1>
            <p style="margin: 10px 0 0; font-size: 16px; opacity: 0.9;">لقد تلقينا طلبك لإعادة تعيين كلمة المرور الخاصة بحسابك</p>
        </div>

        <!-- Content -->
        <div style="padding: 35px 30px;">
            <p style="font-size: 16px; margin-bottom: 20px; color: #555;">
                عزيزنا المستخدم،
            </p>

            <p style="font-size: 16px; margin-bottom: 25px; color: #555;">
                لقد تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك.
                يرجى استخدام رمز التحقق الموضح أدناه لإكمال عملية إعادة التعيين:
            </p>

            <!-- Code Display -->
            <div style="background: #fff7f7; border: 2px solid #f44336; padding: 25px; text-align: center; margin: 30px 0; border-radius: 10px; border-style: dashed;">
                <h3 style="color: #d32f2f; margin: 0 0 12px; font-size: 18px; font-weight: 600;">رمز التحقق</h3>
                <div style="font-size: 36px; font-weight: bold; letter-spacing: 10px; color: #d32f2f; background: #ffffff; padding: 18px; border-radius: 8px; direction: ltr; border: 1px solid #ffcdd2;">
                    {{$code}}
                </div>
            </div>

            <!-- Important Notes -->
            <div style="background: #ffebee; border-right: 4px solid #f44336; padding: 20px; margin: 25px 0; border-radius: 8px;">
                <h4 style="color: #d32f2f; margin: 0 0 12px; font-size: 16px; font-weight: 600;">📝 ملاحظات هامة:</h4>
                <ul style="margin: 0; padding-right: 20px; font-size: 14px; color: #666;">
                    <li style="margin-bottom: 8px;">هذا الرمز ساري المفعول لمدة <strong>30 دقيقة</strong> فقط من وقت إرسال هذه الرسالة</li>
                    <li style="margin-bottom: 8px;"><strong>لا تشارك هذا الرمز مع أي شخص</strong> لأسباب أمنية</li>
                    <li style="margin-bottom: 8px;">إذا لم تطلب إعادة تعيين كلمة المرور، يرجى تجاهل هذه الرسالة</li>
                    <li>تأكد من تحديث كلمة المرور إلى كلمة قوية وفريدة</li>
                </ul>
            </div>

            <!-- Security Warning -->
            <div style="background: #fff3e0; border: 1px solid #ffb74d; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0;">
                <p style="margin: 0; font-size: 14px; color: #e65100;">
                    🔒 <strong>نصيحة أمنية:</strong> ننصحك دائماً باستخدام كلمات مرور قوية تحتوي على أحرف وأرقام ورموز
                </p>
            </div>

            <!-- Support -->
            <p style="font-size: 14px; color: #666; margin-top: 25px;">
                في حال واجهتك أي صعوبة في تفعيل الحساب، لا تتردد في التواصل مع دعم العملاء
                <h6>support@watan-food-chain.com</h6>
            </p>
        </div>

        <!-- Footer -->
        <div style="background: #f8f8f8; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;">
            <p style="margin: 0; font-size: 12px; color: #999;">
                تم إرسال هذه الرسالة تلقائياً من نظامنا. يرجى عدم الرد على هذا البريد الإلكتروني.
            </p>
            {{-- <p style="margin: 8px 0 0; font-size: 12px; color: #999;">
                © 2024 جميع الحقوق محفوظة - اسم شركتك
            </p> --}}
        </div>
    </div>
</body>
</html>
