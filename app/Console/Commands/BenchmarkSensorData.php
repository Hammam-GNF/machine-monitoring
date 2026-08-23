<?php

namespace App\Console\Commands;

use App\Models\Machine;
use App\Services\ProductionReportService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:benchmark-sensor-data')]
#[Description('Benchmark sensor data queries for performance testing')]
class BenchmarkSensorData extends Command
{
    public function handle(): int
    {
        $this->info('Running sensor data query benchmarks...');
        $this->newLine();

        $this->line(
            'Sensor data records: ' .
            number_format(
                \App\Models\SensorData::query()->count()
            )
        );

        $this->line(
            'Machines: ' .
            number_format(Machine::query()->count())
        );

        $this->newLine();

        $this->benchmark(
            'Latest sensor data per machine',
            function () {
                return Machine::query()
                    ->with('latestSensorData')
                    ->get();
            }
        );

        $this->benchmark(
            'Production aggregation by day',
            function () {
                return app(ProductionReportService::class)
                    ->aggregateByDay();
            }
        );

        $this->benchmark(
            'Production aggregation by month',
            function () {
                return app(ProductionReportService::class)
                    ->aggregateByMonth();
            }
        );

        $this->benchmark(
            'Production aggregation by date range',
            function () {
                return app(ProductionReportService::class)
                    ->aggregateByDay(
                        dateFrom: now()->subDays(23)->toDateString(),
                        dateTo: now()->toDateString(),
                    );
            }
        );

        $this->benchmark(
            'Production aggregation by date range and shift',
            function () {
                return app(ProductionReportService::class)
                    ->aggregateByDay(
                        dateFrom: now()->subDays(23)->toDateString(),
                        dateTo: now()->toDateString(),
                        shift: 1,
                    );
            }
        );

        $this->newLine();
        $this->info('Benchmark completed.');

        return self::SUCCESS;
    }

    private function benchmark(
        string $name,
        callable $callback
    ): void {
        $startedAt = hrtime(true);

        $result = $callback();

        $durationMs = (hrtime(true) - $startedAt) / 1_000_000;

        $count = $result instanceof \Illuminate\Support\Collection
            ? $result->count()
            : null;

        $this->line(sprintf(
            '%-50s %10.2f ms%s',
            $name,
            $durationMs,
            $count !== null
                ? sprintf(' (%s rows)', number_format($count))
                : ''
        ));
    }
}
