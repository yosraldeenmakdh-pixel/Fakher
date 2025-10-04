<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckCodeRequest extends FormRequest
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
            'code' => [
                'required',
                'numeric',
                'digits:6',
                'exists:codes,code'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'كود التحقق مطلوب.',
            'code.numeric' => 'يجب أن يكون كود التحقق رقماً.',
            'code.digits' => 'يجب أن يتكون كود التحقق من 6 أرقام.',
            'code.exists' => 'الكود غير صحيح.',
        ];
    }
}
