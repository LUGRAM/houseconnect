<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Dashboard\DashboardService;

class DashboardController extends Controller
{
    public function landlordSummary(DashboardService $dashboardService)
    {
        return response()->json([
            'data' => $dashboardService->landlordSummary(),
        ]);
    }
    
    public function clientSummary(DashboardService $dashboardService)
    {
        return response()->json([
            'data' => $dashboardService->clientSummary(),
        ]);
    }

    public function adminSummary(DashboardService $dashboardService)
    {
        return response()->json([
            'data' => $dashboardService->adminSummary(),
        ]);
    }
}
