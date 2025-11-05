<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
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
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'rating.required' => 'التقييم مطلوب',
            'rating.between' => 'التقييم يجب أن يكون بين 1 و 5',
            'comment.max' => 'التعليق يجب ألا يتجاوز 1000 حرف',
        ];
    }
}
