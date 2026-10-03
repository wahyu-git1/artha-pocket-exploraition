<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'type' => ['required', 'string', 'in:expense,income'],
            'bucket' => ['nullable', 'string', 'in:need,want'],
            'icon' => ['nullable', 'string', 'max:40'],
            'color' => ['nullable', 'string', 'size:7'], // e.g. #FF0000
        ];
    }
}
