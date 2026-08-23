<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardPollingController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search',
            'status',
            'active',
            'maintenance',
        ]);

        $machines = $this->dashboardService->getMachines($filters);

        $statistics = $this->dashboardService->getStatistics($machines);

        return response()->json([
            'machines' => $machines->map(fn ($machine) => [
                'code' => $machine->code,
                'name' => $machine->name,
                'location' => $machine->location,
                'is_active' => $machine->is_active,
                'status' => $machine->latestSensorData?->status,
                'temperature' => $machine->latestSensorData?->temperature !== null
                    ? (float) $machine->latestSensorData->temperature
                    : null,
                'output' => $machine->latestSensorData?->output,
                'maintenance' => $machine->openMaintenanceRecord !== null,
            ])->values(),

            'stats' => [
                'total' => $statistics['total'],
                'active' => $statistics['active'],
                'maintenance' => $statistics['maintenance'],
            ],
        ]);
    }
}
