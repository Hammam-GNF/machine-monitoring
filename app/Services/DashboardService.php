<?php

namespace App\Services;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Machine>
     */
    public function getMachines(array $filters = []): Collection
    {
        return Machine::query()
            ->with([
                'latestSensorData',
                'openMaintenanceRecord',
            ])

            // Search
            ->when(
                $filters['search'] ?? null,
                fn (Builder $query, string $search) => $query->where(
                    fn (Builder $query) => $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                )
            )

            // Location
            ->when(
                ($filters['location'] ?? '') !== '',
                fn (Builder $query) => $query->where(
                    'location',
                    $filters['location']
                )
            )

            // Machine Type
            ->when(
                ($filters['machine_type'] ?? '') !== '',
                fn (Builder $query) => $query->where(
                    'machine_type',
                    $filters['machine_type']
                )
            )

            // Status
            ->when(
                ($filters['status'] ?? '') !== '',
                function (Builder $query) use ($filters) {
                    $status = $filters['status'];

                    if ($status === 'inactive') {
                        $query->where('is_active', false);

                        return;
                    }

                    if (in_array($status, ['ON', 'OFF'], true)) {
                        $query
                            ->where('is_active', true)
                            ->whereHas(
                                'latestSensorData',
                                fn (Builder $query) => $query->where(
                                    'status',
                                    $status
                                )
                            );
                    }
                }
            )

            // Maintenance
            ->when(
                ($filters['maintenance'] ?? '') !== '',
                function (Builder $query) use ($filters) {
                    $maintenance = $filters['maintenance'];

                    if ($maintenance === 'needs_maintenance') {
                        $query->whereHas('openMaintenanceRecord');

                        return;
                    }

                    if ($maintenance === 'normal') {
                        $query->whereDoesntHave('openMaintenanceRecord');
                    }
                }
            )

            ->latest()
            ->get();
    }

    /**
     * @return array{
     *     locations: list<string>,
     *     machineTypes: list<string>
     * }
     */
    public function getFilterOptions(): array
    {
        return [
            'locations' => Machine::query()
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->distinct()
                ->orderBy('location')
                ->pluck('location')
                ->values()
                ->all(),

            'machineTypes' => Machine::query()
                ->whereNotNull('machine_type')
                ->where('machine_type', '!=', '')
                ->distinct()
                ->orderBy('machine_type')
                ->pluck('machine_type')
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  Collection<int, Machine>  $machines
     * @return array{total: int, active: int, maintenance: int}
     */
    public function getStatistics(Collection $machines): array
    {
        return [
            'total' => $machines->count(),

            'active' => $machines
                ->where('is_active', true)
                ->count(),

            'maintenance' => $machines
                ->filter(
                    fn (Machine $machine) => $machine->openMaintenanceRecord !== null
                )
                ->count(),
        ];
    }
}
