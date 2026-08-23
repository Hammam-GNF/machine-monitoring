<?php

namespace App\Services;

use App\Models\SensorData;
use Illuminate\Support\Collection;

class ProductionReportService
{
    public function aggregateByDay(): Collection
    {
        return SensorData::query()
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'output'])
            ->groupBy(
                fn (SensorData $sensorData) => $sensorData->recorded_at
                    ->format('Y-m-d')
            )
            ->map(
                fn (Collection $readings, string $date) => (object) [
                    'date' => $date,
                    'total_output' => $readings->sum('output'),
                ]
            )
            ->values();
    }

    public function aggregateByMonth(): Collection
    {
        return SensorData::query()
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'output'])
            ->groupBy(
                fn (SensorData $sensorData) => $sensorData->recorded_at
                    ->format('Y-m')
            )
            ->map(
                fn (Collection $readings, string $month) => (object) [
                    'year' => (int) substr($month, 0, 4),
                    'month' => (int) substr($month, 5, 2),
                    'total_output' => $readings->sum('output'),
                ]
            )
            ->values();
    }
}
