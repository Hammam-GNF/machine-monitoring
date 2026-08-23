<?php

namespace App\Console\Commands;

use App\Models\Sensor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Signature('app:generate-sensor-data {--sensordata=100000 : Number of sensor data records to generate}')]
#[Description('Generate a large sensor dataset for performance testing')]
class GenerateSensorData extends Command
{
    public function handle(): int
    {
        $total = (int) $this->option('sensordata');

        if ($total < 1) {
            $this->error('The --sensordata value must be greater than 0.');

            return self::FAILURE;
        }

        $sensors = Sensor::query()
            ->where('is_active', true)
            ->get(['id', 'machine_id']);

        if ($sensors->isEmpty()) {
            $this->error('No active sensors found.');

            return self::FAILURE;
        }

        $chunkSize = 1000;
        $generated = 0;

        $this->info("Generating {$total} sensor data records...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        while ($generated < $total) {
            $count = min($chunkSize, $total - $generated);
            $now = now();

            $rows = [];

            for ($i = 0; $i < $count; $i++) {
                $sensor = $sensors->random();
                $recordedAt = now()->subDays(random_int(0, 30))
                    ->subSeconds(random_int(0, 86399));

                $rows[] = [
                    'event_id' => Str::uuid()->toString(),
                    'machine_id' => $sensor->machine_id,
                    'sensor_id' => $sensor->id,
                    'status' => random_int(0, 1) === 1 ? 'ON' : 'OFF',
                    'temperature' => random_int(5000, 9500) / 100,
                    'output' => random_int(80, 150),
                    'recorded_at' => $recordedAt,
                    'received_at' => $now,
                    'created_at' => $now,
                ];
            }

            DB::table('sensor_data')->insert($rows);

            $generated += $count;
            $bar->advance($count);
        }

        $bar->finish();

        $this->newLine(2);
        $this->info("Successfully generated {$total} sensor data records.");

        return self::SUCCESS;
    }
}
