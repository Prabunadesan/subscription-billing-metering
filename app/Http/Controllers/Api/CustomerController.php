<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function store(
        StoreCustomerRequest $request
    ): JsonResponse {
        $customer = Customer::create([
            'merchant_id' => $request->integer('merchant_id'),
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
        ]);

        return response()->json([
            'message' => 'Customer created successfully.',
            'data' => $customer,
        ], 201);
    }
}