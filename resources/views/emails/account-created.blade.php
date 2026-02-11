<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم إنشاء حسابك في وطن فود</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
        }

        body {
            background-color: #fef3c7;
            color: #1f2937;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1.6;
        }

        .email-wrapper {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .email-container {
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #fbbf24;
        }

        /* الهيدر */
        .header {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .logo {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .tagline {
            font-size: 16px;
            opacity: 0.95;
            font-weight: 400;
        }

        .greeting {
            font-size: 28px;
            font-weight: 700;
            margin-top: 20px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* المحتوى */
        .content {
            padding: 40px 30px;
            text-align: center;
        }

        .user-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px dashed #fde68a;
        }

        .user-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            margin: 0 auto 15px;
        }

        .user-name {
            font-size: 22px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .user-role {
            font-size: 16px;
            color: #6b7280;
        }

        .message {
            font-size: 17px;
            color: #4b5563;
            margin-bottom: 30px;
            line-height: 1.7;
        }

        .action-box {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 16px;
            padding: 30px 20px;
            margin: 30px 0;
        }

        .instruction {
            font-size: 18px;
            color: #92400e;
            font-weight: 600;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            color: white;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .reset-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(245, 158, 11, 0.4);
        }

        .expiry {
            margin-top: 20px;
            font-size: 14px;
            color: #92400e;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .security-tip {
            background: #fef3c7;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            border: 1px solid #f59e0b;
        }

        .security-tip p {
            color: #92400e;
            font-size: 15px;
            line-height: 1.6;
        }

        .support-section {
            background: #fef3c7;
            border-radius: 16px;
            padding: 25px;
            margin-top: 30px;
            border: 1px solid #f59e0b;
        }

        .support-title {
            color: #92400e;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .support-text {
            color: #92400e;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .support-email {
            color: #f59e0b;
            font-weight: 600;
            text-decoration: none;
            font-size: 16px;
            display: inline-block;
            padding: 10px 20px;
            background: white;
            border-radius: 8px;
            border: 1px solid #f59e0b;
            transition: all 0.3s ease;
        }

        .support-email:hover {
            background: #f59e0b;
            color: white;
        }

        /* الفوتر */
        .footer {
            background: #1f2937;
            color: #d1d5db;
            padding: 30px 20px;
            text-align: center;
            border-top: 4px solid #f59e0b;
        }

        .copyright {
            font-size: 14px;
            color: #9ca3af;
            line-height: 1.6;
        }

        /* علامات */
        .badges {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .badge {
            background: #fef3c7;
            color: #92400e;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid #f59e0b;
        }

        /* استجابة للجوال */
        @media (max-width: 640px) {
            body {
                padding: 10px;
            }

            .header, .content, .footer {
                padding: 30px 20px;
            }

            .logo {
                font-size: 32px;
            }

            .greeting {
                font-size: 24px;
            }

            .user-name {
                font-size: 20px;
            }

            .instruction {
                font-size: 16px;
            }

            .reset-button {
                padding: 14px 35px;
                font-size: 16px;
                width: 100%;
                max-width: 280px;
            }

            .action-box {
                padding: 25px 15px;
            }
        }

        /* تحسينات للقراءة */
        .text-center {
            text-align: center;
        }

        .mb-10 { margin-bottom: 10px; }
        .mb-20 { margin-bottom: 20px; }
        .mb-30 { margin-bottom: 30px; }

        /* تأثيرات بسيطة */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .email-container {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- الهيدر -->
            <div class="header">
                <div class="logo">وطن فود</div>
                <div class="tagline">رحلة طعام لا تُنسى</div>
                <div class="greeting mb-20">مرحباً بك في عائلة وطن فود</div>
            </div>

            <!-- المحتوى -->
            <div class="content">
                <!-- قسم المستخدم -->
                <div class="user-section">
                    <div class="user-icon">👋</div>
                    <div class="user-name">عزيزي/عزيزتنا {{ $user->name }}</div>
                    <div class="user-role">حسابك الرسمي في منصة وطن فود</div>
                </div>

                <!-- الرسالة الرئيسية -->
                <div class="message mb-30">
                    يسرنا إعلامك بأن حسابك الرسمي في منصة <strong>وطن فود</strong> قد تم إنشاؤه بنجاح من قبل فريق الإدارة لدينا.
                </div>

                <!-- رابط إعادة التعيين -->
                <div class="action-box mb-30">
                    <div class="instruction mb-20">
                        لبدء رحلتك معنا، يجب عليك إعادة تعيين كلمة المرور الخاصة بك ثم تسجيل دخولك إلى النظام.
                    </div>

                    <a href="{{ $resetLink }}" class="reset-button mb-20">
                        إعادة تعيين كلمة المرور
                    </a>

                    <div class="instruction mb-20">
                        ⏱️ تنتهي صلاحية هذا الرابط بعد 24 ساعة
                    </div>
                </div>

                <!-- نصيحة أمان -->
                <div class="security-tip mb-30">
                    <p><strong>نصيحة أمان:</strong> ننصحك باختيار كلمة مرور قوية تحتوي على أحرف كبيرة وصغيرة وأرقام ورموز خاصة وبدون مسافات.</p>
                </div>

                <!-- قسم الدعم -->
                <div class="support-section">
                    <div class="instruction mb-20">
                        فريق الدعم على أتم الاستعداد
                    </div>

                    <div class="support-text mb-20">
                        إذا واجهتك أي صعوبيات في عملية إعادة تعيين كلمة المرور، أو لديك أي استفسارات، فريق الدعم لدينا جاهز لمساعدتك على مدار الساعة.
                    </div>

                    <a href="mailto:support@watan-food-chain.com" class="support-email">
                        support@watan-food-chain.com
                    </a>
                </div>
            </div>

            <!-- الفوتر -->
            <div class="footer">
                <div class="copyright">
                    © {{ date('Y') }} وطن فود - جميع الحقوق محفوظة<br>
                    هذا البريد الإلكتروني مرسل تلقائياً من نظام وطن فود، يرجى عدم الرد عليه.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
