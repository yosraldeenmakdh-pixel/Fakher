<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|size:10|regex:/^09[0-9]{8}$/',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
        ] , [
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.size' => 'رقم الهاتف يجب أن يتكون من 10 أرقام بالضبط',
            'phone.regex' => 'رقم الهاتف يجب أن يبدأ بـ 09 ويحتوي على أرقام فقط',

            'subject.required' => 'الموضوع مطلوب',
            'subject.string' => 'الموضوع يجب أن يكون نصاً',
            'subject.min' => 'الموضوع يجب أن يحتوي على الأقل على 3 أحرف',
            'subject.max' => 'الموضوع يجب ألا يتجاوز 255 حرف',

            'message.required' => 'الرسالة مطلوبة',
            'message.string' => 'الرسالة يجب أن تكون نصاً',
            'message.min' => 'الرسالة يجب أن تحتوي على الأقل على 10 أحرف',
            'message.max' => 'الرسالة يجب ألا تتجاوز 1000 حرف',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            $complaint = $user->complaints()->create([
                'phone' => $request->phone,
                'subject' => $request->subject,
                'message' => $request->message,
            ]);

            return response()->json([
                'success' => true,
                'data' => $complaint,
                'message' => 'تم إرسال شكواك بنجاح'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الشكوى',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
