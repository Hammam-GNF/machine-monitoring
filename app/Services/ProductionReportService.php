<?php

namespace App\Services;

use App\Models\SensorData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductionReportService
{
    public function aggregateByDay(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $shift = null,
        ?int $machineId = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        $dateExpression = $this->dateExpression();

        return $this->buildQuery($dateFrom, $dateTo, $shift, $machineId)
            ->select('machine_id')
            ->selectRaw("{$dateExpression} as date")
            ->selectRaw('SUM(output) as total_output')
            ->with('machine:id,code,name')
            ->groupBy('machine_id')
            ->groupByRaw($dateExpression)
            ->orderBy('date')
            ->orderBy('machine_id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function aggregateByMonth(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $shift = null,
        ?int $machineId = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        $monthExpression = $this->monthExpression();
        $yearExpression = $this->yearExpression();

        return $this->buildQuery($dateFrom, $dateTo, $shift, $machineId)
            ->select('machine_id')
            ->selectRaw("{$yearExpression} as year")
            ->selectRaw("{$monthExpression} as month")
            ->selectRaw('SUM(output) as total_output')
            ->with('machine:id,code,name')
            ->groupBy('machine_id')
            ->groupByRaw("{$yearExpression}, {$monthExpression}")
            ->orderBy('year')
            ->orderBy('month')
            ->orderBy('machine_id')
            ->paginate($perPage)
            ->withQueryString();
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
        $query = $this->buildQuery(
            $dateFrom,
            $dateTo,
            $shift,
            $machineId
        );

        $totalOutput = (int) (clone $query)->sum('output');

        $totalReadings = (int) (clone $query)->count();

        $onReadings = (int) (clone $query)
            ->where('status', 'ON')
            ->count();

        $hours = $this->countRecordedHours(
            $dateFrom,
            $dateTo,
            $shift,
            $machineId
        );

        if ($totalReadings === 0) {
            return [
                'total_output' => 0,
                'average_output_per_hour' => 0.0,
                'uptime_percentage' => 0.0,
                'downtime_percentage' => 0.0,
            ];
        }

        $uptime = ($onReadings / $totalReadings) * 100;

        return [
            'total_output' => $totalOutput,
            'average_output_per_hour' => round(
                $totalOutput / max(1, $hours),
                2
            ),
            'uptime_percentage' => round($uptime, 2),
            'downtime_percentage' => round(100 - $uptime, 2),
        ];
    }

    private function countRecordedHours(
        ?string $dateFrom,
        ?string $dateTo,
        ?int $shift,
        ?int $machineId
    ): int {
        $driver = DB::connection()->getDriverName();

        $query = $this->buildQuery(
            $dateFrom,
            $dateTo,
            $shift,
            $machineId
        );

        $hourExpression = match ($driver) {
            'sqlsrv' => 'CONVERT(varchar(13), recorded_at, 120)',
            'sqlite' => "strftime('%Y-%m-%d %H', recorded_at)",
            default => "DATE_FORMAT(recorded_at, '%Y-%m-%d %H')",
        };

        return (int) $query
            ->selectRaw(
                "COUNT(DISTINCT {$hourExpression}) as hours"
            )
            ->value('hours');
    }

    /**
     * @return Builder<SensorData>
     */
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

    /**
     * @param  Builder<SensorData>  $query
     */
    private function applyShiftFilter(
        Builder $query,
        int $shift
    ): void {
        match ($shift) {
            1 => $query
                ->whereTime('recorded_at', '>=', '06:00:00')
                ->whereTime('recorded_at', '<', '14:00:00'),

            2 => $query
                ->whereTime('recorded_at', '>=', '14:00:00')
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
