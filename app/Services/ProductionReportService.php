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
        ?int $shift = null
    ): Collection {
        return $this->buildQuery($dateFrom, $dateTo, $shift)
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
        ?int $shift = null
    ): Collection {
        $dateExpression = $this->dateExpression();
        $monthExpression = $this->monthExpression();
        $yearExpression = $this->yearExpression();

        return $this->buildQuery($dateFrom, $dateTo, $shift)
            ->selectRaw("{$yearExpression} as year")
            ->selectRaw("{$monthExpression} as month")
            ->selectRaw('SUM(output) as total_output')
            ->groupByRaw(
                "{$yearExpression}, {$monthExpression}"
            )
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    /** @return Builder<SensorData> */
    private function buildQuery(
        ?string $dateFrom,
        ?string $dateTo,
        ?int $shift
    ): Builder {
        return SensorData::query()
            ->when(
                $dateFrom,
                fn (Builder $query, string $date) => $query->whereDate('recorded_at', '>=', $date)
            )
            ->when(
                $dateTo,
                fn (Builder $query, string $date) => $query->whereDate('recorded_at', '<=', $date)
            )
            ->when(
                $shift,
                fn (Builder $query, int $shift) => $this->applyShiftFilter($query, $shift)
            );
    }

    /** @param Builder<SensorData> $query */
    private function applyShiftFilter(
        Builder $query,
        int $shift
    ): void {
        match ($shift) {
            1 => $query->whereTime(
                'recorded_at',
                '>=',
                '06:00:00'
            )->whereTime(
                'recorded_at',
                '<',
                '14:00:00'
            ),

            2 => $query->whereTime(
                'recorded_at',
                '>=',
                '14:00:00'
            )->whereTime(
                'recorded_at',
                '<',
                '22:00:00'
            ),

            3 => $query->where(function (Builder $query) {
                $query
                    ->whereTime(
                        'recorded_at',
                        '>=',
                        '22:00:00'
                    )
                    ->orWhereTime(
                        'recorded_at',
                        '<',
                        '06:00:00'
                    );
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
