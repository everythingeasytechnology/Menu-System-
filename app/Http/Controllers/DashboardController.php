<?php

namespace App\Http\Controllers;

use App\Services\OwnerDashboardService;

class DashboardController extends Controller
{
    public function __construct(private readonly OwnerDashboardService $dashboardService)
    {
    }

    public function index()
    {
        return view('dashboard', $this->dashboardService->dashboardData(auth()->user()));
    }
}
