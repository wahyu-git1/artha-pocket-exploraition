<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'date_format:Y-m'],
            'income_id' => ['nullable', 'uuid', 'exists:incomes,id'],
            'template_code' => ['nullable', 'string', 'max:20'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.bucket' => ['required', 'string', 'in:need,want,saving,investment'],
            'items.*.percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
