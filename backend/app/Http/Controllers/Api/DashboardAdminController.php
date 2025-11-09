<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;

class DashboardAdminController extends Controller
{
    public function summary(DashboardService $dashboardService)
    {
        return response()->json([
            'data' => $dashboardService->globalSummary(),
        ]);
    }
}
