<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {
    }

    public function show(Merchant $merchant): JsonResponse
    {
        return response()->json([
            'message' => 'Dashboard retrieved successfully.',
            'data' => $this->dashboardService->get($merchant),
        ]);
    }
}