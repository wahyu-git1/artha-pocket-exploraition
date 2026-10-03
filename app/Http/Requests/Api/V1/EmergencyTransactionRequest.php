<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class EmergencyTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isWithdrawal = $this->route()->getActionMethod() === 'withdrawal';
        
        $rules = [
            'amount' => ['required', 'integer', 'min:1'],
            'occurred_at' => ['required', 'date_format:Y-m-d'],
            'reason' => ['nullable', 'string', 'max:255'],
            'client_id' => ['required', 'uuid'],
        ];

        if ($isWithdrawal) {
            $rules['reason'] = ['required', 'string', 'max:255'];
            // Withdrawal might not need income_id since it goes out
            $rules['income_id'] = ['nullable', 'uuid', 'exists:incomes,id']; 
        } else {
            // Deposit needs income_id
            $rules['income_id'] = ['required', 'uuid', 'exists:incomes,id'];
        }

        return $rules;
    }
}
