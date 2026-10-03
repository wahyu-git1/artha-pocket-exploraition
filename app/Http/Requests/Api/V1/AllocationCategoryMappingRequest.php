<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AllocationCategoryMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mappings' => ['required', 'array'],
            'mappings.*.category_id' => ['required', 'uuid', 'exists:categories,id'],
            'mappings.*.bucket' => ['required', 'string', 'in:need,want,saving,investment'],
        ];
    }
}
