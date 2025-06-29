<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePublisherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:publishers,name',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên nhà xuất bản không được để trống.',
            'name.max' => 'Tên nhà xuất bản không được vượt quá 100 ký tự.',
            'name.unique' => 'Tên nhà xuất bản đã tồn tại.',
        ];
    }
}
