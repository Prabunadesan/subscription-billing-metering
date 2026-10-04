<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePlanRequest;
use App\Models\Customer;
use App\Models\Plan;
use App\Services\PlanChangeService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\JsonResponse;

class PlanChangeController extends Controller
{
    public function __construct(
        private readonly PlanChangeService $planChangeService
    ) {
    }

    public function store(
        ChangePlanRequest $request
    ): JsonResponse {
        $customer = Customer::findOrFail(
            $request->integer('customer_id')
        );

        $plan = Plan::findOrFail(
            $request->integer('plan_id')
        );

        try {
            $subscription = $this->planChangeService->change(
                customer: $customer,
                newPlan: $plan,
                effectiveAt: Carbon::parse(
                    $request->input('effective_at')
                ),
            );

            return response()->json([
                'message' => 'Plan changed successfully.',
                'data' => [
                    'subscription_id' => $subscription->id,
                    'customer_id' => $subscription->customer_id,
                    'plan_id' => $subscription->plan_id,
                    'plan_name' => $subscription->plan->name,
                    'starts_at' => $subscription->starts_at,
                    'ends_at' => $subscription->ends_at,
                    'status' => $subscription->status,
                ],
            ], 201);

        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}