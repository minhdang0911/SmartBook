<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1|max:999'
        ];
    }

    public function messages()
    {
        return [
            'book_id.required' => 'Vui lòng chọn sách',
            'book_id.exists' => 'Sách không tồn tại',
            'quantity.required' => 'Vui lòng nhập số lượng',
            'quantity.integer' => 'Số lượng phải là số nguyên',
            'quantity.min' => 'Số lượng tối thiểu là 1',
            'quantity.max' => 'Số lượng tối đa là 999'
        ];
    }
}
