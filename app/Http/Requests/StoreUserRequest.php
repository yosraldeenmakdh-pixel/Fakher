<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name'=>'required|string|max:255|unique:users,name' ,
            'email'=>'required|email|unique:users,email' ,
            'password'=>'required|min:7|confirmed' ,
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'حقل الاسم مطلوب.',
            'name.unique' => 'هذا الاسم مستخدم من قبل.',
            'email.required' => 'حقل البريد الإلكتروني مطلوب.',
            'email.email' => 'يجب أن يكون البريد الإلكتروني صالحاً.',
            'email.unique' => 'هذا البريد الإلكتروني مستخدم من قبل.',
            'password.required' => 'حقل كلمة المرور مطلوب.',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
        ];
    }
}
