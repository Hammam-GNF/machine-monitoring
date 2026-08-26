<?php

namespace App\Console\Commands;

use App\Models\Sensor;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Signature(
    'app:generate-sensor-data
    {--count=100000 : Number of sensor data records to generate}
    {--chunk=200 : Number of records inserted per batch}
    {--days=30 : Historical period in days}'
)]
#[Description('Generate large-scale historical IoT sensor data for performance testing')]
class GenerateSensorData extends Command
{
    public function handle(): int
    {
        $count = (int) $this->option('count');
        $chunkSize = (int) $this->option('chunk');
        $days = (int) $this->option('days');

        if ($count < 1) {
            $this->error('Count must be greater than 0.');

            return self::FAILURE;
        }

        if ($chunkSize < 1) {
            $this->error('Chunk size must be greater than 0.');

            return self::FAILURE;
        }

        if ($days < 1) {
            $this->error('Days must be greater than 0.');

            return self::FAILURE;
        }

        $sensors = Sensor::query()
            ->where('is_active', true)
            ->get(['id', 'machine_id']);

        if ($sensors->isEmpty()) {
            $this->error('No active sensors found.');

            return self::FAILURE;
        }

        $now = CarbonImmutable::now();
        $startTimestamp = (int) $now->subDays($days)->timestamp;
        $endTimestamp = (int) $now->timestamp;

        $startedAt = hrtime(true);
        $generated = 0;

        $this->info(
            sprintf(
                'Generating %s sensor data records in batches of %s...',
                number_format($count),
                number_format($chunkSize)
            )
        );

        while ($generated < $count) {
            $batchSize = min($chunkSize, $count - $generated);
            $rows = [];

            for ($i = 0; $i < $batchSize; $i++) {
                $sensor = $sensors->random();

                $recordedAt = CarbonImmutable::createFromTimestamp(
                    random_int($startTimestamp, $endTimestamp)
                );

                $receivedAt = $recordedAt->addSeconds(
                    random_int(0, 30)
                );

                if ($receivedAt->greaterThan($now)) {
                    $receivedAt = $now;
                }

                $rows[] = [
                    'event_id' => Str::uuid()->toString(),
                    'machine_id' => $sensor->machine_id,
                    'sensor_id' => $sensor->id,
                    'status' => random_int(0, 1) === 1 ? 'ON' : 'OFF',
                    'temperature' => number_format(
                        random_int(5000, 9500) / 100,
                        2,
                        '.',
                        ''
                    ),
                    'output' => random_int(80, 150),
                    'recorded_at' => $recordedAt,
                    'received_at' => $receivedAt,
                    'created_at' => $receivedAt,
                ];
            }

            DB::table('sensor_data')->insert($rows);

            $generated += $batchSize;

            $this->output->write(
                "\rGenerated: ".number_format($generated).' / '.number_format($count)
            );
        }

        $durationMs = (hrtime(true) - $startedAt) / 1_000_000;
        $durationSeconds = $durationMs / 1000;

        $this->newLine(2);

        $this->info('Sensor data generation completed.');

        $this->table(
            ['Metric', 'Value'],
            [
                ['Records generated', number_format($generated)],
                ['Batch size', number_format($chunkSize)],
                ['Historical period', $days.' days'],
                ['Duration', number_format($durationSeconds, 2).' seconds'],
                [
                    'Throughput',
                    number_format(
                        $generated / max($durationSeconds, 0.001),
                        0
                    ).' rows/sec',
                ],
            ]
        );

        return self::SUCCESS;
    }
}
