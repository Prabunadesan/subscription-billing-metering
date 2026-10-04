<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
            ],

            'plan_id' => [
                'required',
                'integer',
                'exists:plans,id',
            ],

            'effective_at' => [
                'required',
                'date',
            ],
        ];
    }
}