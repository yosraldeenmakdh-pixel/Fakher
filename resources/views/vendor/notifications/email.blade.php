
<body style="font-family: 'Tajawal', 'Segoe UI', sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5; text-align: center; unicode-bidi: embed;">
    <div style="background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <div style="padding: 35px 30px;">
            <p style="font-size: 16px; margin-bottom: 20px; color: #555; text-align: center;">
                عزيزي المستخدم
            </p>

            <p style="font-size: 16px; margin-bottom: 25px; color: #555; text-align: center;">
                لقد تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك<br>
                يرجى النقر على الزر الموضح أدناه لإكمال عملية إعادة التعيين
            </p>
        </div>


<x-mail::message>
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset



</x-mail::message>


 </div>
</div>
</body>
