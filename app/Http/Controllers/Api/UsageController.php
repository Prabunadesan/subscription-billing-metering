<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsageRequest;
use App\Models\Customer;
use App\Services\UsageService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class UsageController extends Controller
{
    public function __construct(
        private readonly UsageService $usageService
    ) {
    }

    public function store(StoreUsageRequest $request): JsonResponse
    {
        $customer = Customer::findOrFail(
            $request->integer('customer_id')
        );

        try {
            $event = $this->usageService->record(
                customer: $customer,
                quantity: $request->integer('quantity'),
                occurredAt: Carbon::parse($request->input('occurred_at')),
                idempotencyKey: $request->string('idempotency_key')->toString(),
            );

            return response()->json([
                'message' => 'Usage recorded successfully.',
                'data' => [
                    'id' => $event->id,
                    'customer_id' => $event->customer_id,
                    'quantity' => $event->quantity,
                    'usage_date' => $event->usage_date->toDateString(),
                    'idempotency_key' => $event->idempotency_key,
                ],
            ], 201);

        } catch (\DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}