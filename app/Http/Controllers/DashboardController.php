<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only([
            'search',
            'status',
            'active',
            'maintenance',
        ]);

        $machines = $this->dashboardService->getMachines($filters);

        $statistics = $this->dashboardService->getStatistics($machines);

        return view('dashboard', [
            'machines' => $machines,
            'totalMachines' => $statistics['total'],
            'activeMachines' => $statistics['active'],
            'machinesNeedingMaintenance' => $statistics['maintenance'],
            'search' => $filters['search'] ?? '',
            'status' => $filters['status'] ?? '',
            'maintenance' => $filters['maintenance'] ?? '',
        ]);
    }
}
