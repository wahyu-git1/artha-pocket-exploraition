<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class GoalDepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'income_id' => ['required', 'uuid', 'exists:incomes,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'deposited_at' => ['required', 'date_format:Y-m-d'],
            'note' => ['nullable', 'string', 'max:255'],
            'client_id' => ['required', 'uuid'],
        ];
    }
}
