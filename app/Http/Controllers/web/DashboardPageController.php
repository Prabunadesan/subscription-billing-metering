<?php
namespace App\Http\Controllers\web;

use App\Models\Merchant;
use App\Services\DashboardService;
use Illuminate\View\View;
use App\Http\Controllers\Controller;

class DashboardPageController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {
    }

    public function show(Merchant $merchant)
    {
        $dashboard = $this->dashboardService->getDashboard($merchant);

        return view('dashboard', [
            'merchant' => $merchant,
            'dashboard' => $dashboard,
        ]);
    }
}