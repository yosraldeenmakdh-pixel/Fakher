<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password'=>'required|min:7|confirmed' ,
            'reset_token'=>'required|string|size:60|exists:rest_codes,reset_token' ,
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 7 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'reset_token.required' => 'رمز إعادة التعيين مطلوب',
            'reset_token.exists' => 'رمز إعادة التعيين غير صحيح',
        ];
    }
}
