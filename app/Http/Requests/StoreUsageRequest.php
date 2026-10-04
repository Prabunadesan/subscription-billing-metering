<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsageRequest extends FormRequest
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
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'occurred_at' => [
                'required',
                'date',
            ],
            'idempotency_key' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }
}