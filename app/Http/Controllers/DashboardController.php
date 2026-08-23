<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $machines = Machine::query()
            ->with([
                'latestSensorData',
                'openMaintenanceRecord',
            ])
            ->latest()
            ->get();

        $totalMachines = $machines->count();

        $activeMachines = $machines
            ->where('is_active', true)
            ->count();

        $machinesNeedingMaintenance = $machines
            ->filter(fn (Machine $machine) => $machine->openMaintenanceRecord !== null)
            ->count();

        return view('dashboard', [
            'machines' => $machines,
            'totalMachines' => $totalMachines,
            'activeMachines' => $activeMachines,
            'machinesNeedingMaintenance' => $machinesNeedingMaintenance,
        ]);
    }
}
