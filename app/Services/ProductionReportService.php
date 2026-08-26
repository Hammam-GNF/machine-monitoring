<?php

namespace App\Services;

use App\Models\SensorData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductionReportService
{
    /** @return Collection<int, SensorData> */
    public function aggregateByDay(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $shift = null,
        ?int $machineId = null
    ): Collection {
        return $this->buildQuery($dateFrom, $dateTo, $shift, $machineId)
            ->selectRaw($this->dateExpression().' as date')
            ->selectRaw('SUM(output) as total_output')
            ->groupByRaw($this->dateExpression())
            ->orderBy('date')
            ->get();
    }

    /** @return Collection<int, SensorData> */
    public function aggregateByMonth(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $shift = null,
        ?int $machineId = null
    ): Collection {
        $monthExpression = $this->monthExpression();
        $yearExpression = $this->yearExpression();

        return $this->buildQuery($dateFrom, $dateTo, $shift, $machineId)
            ->selectRaw("{$yearExpression} as year")
            ->selectRaw("{$monthExpression} as month")
            ->selectRaw('SUM(output) as total_output')
            ->groupByRaw("{$yearExpression}, {$monthExpression}")
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    /**
     * @return array{
     *     total_output: int,
     *     average_output_per_hour: float,
     *     uptime_percentage: float,
     *     downtime_percentage: float
     * }
     */
    public function metrics(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $shift = null,
        ?int $machineId = null
    ): array {
        $data = $this->buildQuery(
            $dateFrom,
            $dateTo,
            $shift,
            $machineId
        )->get(['output', 'status', 'recorded_at']);

        if ($data->isEmpty()) {
            return [
                'total_output' => 0,
                'average_output_per_hour' => 0.0,
                'uptime_percentage' => 0.0,
                'downtime_percentage' => 0.0,
            ];
        }

        $totalOutput = (int) $data->sum('output');

        $hours = max(
            1,
            $data->pluck('recorded_at')
                ->map(fn ($date) => $date->format('Y-m-d H'))
                ->unique()
                ->count()
        );

        $totalReadings = $data->count();
        $onReadings = $data->where('status', 'ON')->count();
        $uptime = ($onReadings / $totalReadings) * 100;

        return [
            'total_output' => $totalOutput,
            'average_output_per_hour' => round($totalOutput / $hours, 2),
            'uptime_percentage' => round($uptime, 2),
            'downtime_percentage' => round(100 - $uptime, 2),
        ];
    }

    /** @return Builder<SensorData> */
    private function buildQuery(
        ?string $dateFrom,
        ?string $dateTo,
        ?int $shift,
        ?int $machineId
    ): Builder {
        return SensorData::query()
            ->when(
                $machineId,
                fn (Builder $query, int $machineId) => $query->where(
                    'machine_id',
                    $machineId
                )
            )
            ->when(
                $dateFrom,
                fn (Builder $query, string $date) => $query->whereDate(
                    'recorded_at',
                    '>=',
                    $date
                )
            )
            ->when(
                $dateTo,
                fn (Builder $query, string $date) => $query->whereDate(
                    'recorded_at',
                    '<=',
                    $date
                )
            )
            ->when(
                $shift,
                fn (Builder $query, int $shift) => $this->applyShiftFilter(
                    $query,
                    $shift
                )
            );
    }

    /** @param Builder<SensorData> $query */
    private function applyShiftFilter(
        Builder $query,
        int $shift
    ): void {
        match ($shift) {
            1 => $query->whereTime('recorded_at', '>=', '06:00:00')
                ->whereTime('recorded_at', '<', '14:00:00'),

            2 => $query->whereTime('recorded_at', '>=', '14:00:00')
                ->whereTime('recorded_at', '<', '22:00:00'),

            3 => $query->where(function (Builder $query) {
                $query
                    ->whereTime('recorded_at', '>=', '22:00:00')
                    ->orWhereTime('recorded_at', '<', '06:00:00');
            }),

            default => throw new \InvalidArgumentException(
                'Shift must be 1, 2, or 3.'
            ),
        };
    }

    /** @return literal-string */
    private function dateExpression(): string
    {
        $driver = (string) config(
            'database.connections.'.config('database.default').'.driver'
        );

        return match ($driver) {
            'sqlite' => 'date(recorded_at)',
            'sqlsrv' => 'CAST(recorded_at AS date)',
            default => 'DATE(recorded_at)',
        };
    }

    /** @return literal-string */
    private function monthExpression(): string
    {
        $driver = (string) config(
            'database.connections.'.config('database.default').'.driver'
        );

        return match ($driver) {
            'sqlite' => 'strftime("%m", recorded_at)',
            'sqlsrv' => 'MONTH(recorded_at)',
            default => 'MONTH(recorded_at)',
        };
    }

    /** @return literal-string */
    private function yearExpression(): string
    {
        $driver = (string) config(
            'database.connections.'.config('database.default').'.driver'
        );

        return match ($driver) {
            'sqlite' => 'strftime("%Y", recorded_at)',
            'sqlsrv' => 'YEAR(recorded_at)',
            default => 'YEAR(recorded_at)',
        };
    }
}
