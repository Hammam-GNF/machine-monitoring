<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();
        $maintenance = $request->string('maintenance')->toString();

        $machines = Machine::query()
            ->with([
                'latestSensorData',
                'openMaintenanceRecord',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                if ($status === 'inactive') {
                    $query->where('is_active', false);

                    return;
                }

                if (in_array($status, ['ON', 'OFF'], true)) {
                    $query
                        ->where('is_active', true)
                        ->whereHas('latestSensorData', function ($query) use ($status) {
                            $query->where('status', $status);
                        });
                }
            })
            ->when($maintenance !== '', function ($query) use ($maintenance) {
                if ($maintenance === 'needs_maintenance') {
                    $query->whereHas('openMaintenanceRecord');

                    return;
                }

                if ($maintenance === 'normal') {
                    $query->whereDoesntHave('openMaintenanceRecord');
                }
            })
            ->latest()
            ->get();

        $totalMachines = Machine::count();

        $activeMachines = Machine::where('is_active', true)->count();

        $machinesNeedingMaintenance = Machine::whereHas('openMaintenanceRecord')->count();

        return view('dashboard', [
            'machines' => $machines,
            'totalMachines' => $totalMachines,
            'activeMachines' => $activeMachines,
            'machinesNeedingMaintenance' => $machinesNeedingMaintenance,
            'search' => $search,
            'status' => $status,
            'maintenance' => $maintenance,
        ]);
    }
}
