<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuthorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:100',
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'Tên tác giả không được để trống.',
            'name.max' => 'Tên tác giả không được vượt quá 100 ký tự.',
        ];
    }
}
