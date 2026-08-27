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
            'location',
            'machine_type',
            'status',
            'active',
            'maintenance',
        ]);

        $machines = $this->dashboardService->getMachines($filters);
        $statistics = $this->dashboardService->getStatistics($machines);
        $filterOptions = $this->dashboardService->getFilterOptions();

        return view('dashboard', [
            'machines' => $machines,

            'totalMachines' => $statistics['total'],
            'activeMachines' => $statistics['active'],
            'machinesNeedingMaintenance' => $statistics['maintenance'],

            'search' => $filters['search'] ?? '',
            'location' => $filters['location'] ?? '',
            'machineType' => $filters['machine_type'] ?? '',
            'status' => $filters['status'] ?? '',
            'maintenance' => $filters['maintenance'] ?? '',

            'locations' => $filterOptions['locations'],
            'machineTypes' => $filterOptions['machineTypes'],
        ]);
    }
}
