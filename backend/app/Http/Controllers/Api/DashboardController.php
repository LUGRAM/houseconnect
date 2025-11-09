<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Dashboard\DashboardService;

class DashboardController extends Controller
{
    public function summary(DashboardService $dashboardService)
    {
        return response()->json([
            'data' => $dashboardService->summary(),
        ]);
    }
}
