<?php

namespace App\Console\Commands;

use App\Models\Machine;
use App\Models\SensorData;
use App\Services\ProductionReportService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use InvalidArgumentException;

#[Signature('app:benchmark-sensor-data')]
#[Description('Benchmark sensor data queries for performance testing')]
class BenchmarkSensorData extends Command
{
    public function handle(): int
    {
        $this->info('Running sensor data query benchmarks...');
        $this->newLine();

        $this->line(
            'Sensor data records: '.
            number_format(SensorData::query()->count())
        );

        $this->line(
            'Machines: '.
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
                        dateFrom: now()->subDays(7)->toDateString(),
                        dateTo: now()->toDateString(),
                    );
            }
        );

        $this->benchmark(
            'Production aggregation by date range and shift',
            function () {
                return app(ProductionReportService::class)
                    ->aggregateByDay(
                        dateFrom: now()->subDays(7)->toDateString(),
                        dateTo: now()->toDateString(),
                        shift: 1,
                    );
            }
        );

        $this->info('Benchmark completed.');

        return self::SUCCESS;
    }

    private function benchmark(
        string $name,
        callable $callback,
        int $runs = 3
    ): void {
        if ($runs < 1) {
            throw new InvalidArgumentException(
                'Benchmark runs must be at least 1.'
            );
        }

        $result = null;
        $times = [];

        for ($i = 0; $i < $runs; $i++) {
            $startedAt = hrtime(true);

            $result = $callback();

            $times[] = (hrtime(true) - $startedAt) / 1_000_000;
        }

        $average = array_sum($times) / count($times);

        $count = $result instanceof Collection
            ? $result->count()
            : null;

        $this->line($name);

        foreach ($times as $index => $time) {
            $this->line(sprintf(
                '  Run %d:     %8.2f ms',
                $index + 1,
                $time
            ));
        }

        $this->line(sprintf(
            '  Average:    %8.2f ms',
            $average
        ));

        $this->line(sprintf(
            '  Min:        %8.2f ms',
            min($times)
        ));

        $this->line(sprintf(
            '  Max:        %8.2f ms',
            max($times)
        ));

        if ($count !== null) {
            $this->line(
                '  Result rows: '.number_format($count)
            );
        }

        $this->newLine();
    }
}
