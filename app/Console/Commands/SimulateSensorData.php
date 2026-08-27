<?php

namespace App\Console\Commands;

use App\Models\Machine;
use App\Models\Sensor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

#[Signature('app:simulate-sensor-data')]
#[Description('Simulate IoT sensor data and send it to the sensor API')]
class SimulateSensorData extends Command
{
    public function handle(): int
    {
        $sensors = Sensor::query()
            ->where('is_active', true)
            ->with('machine')
            ->get();

        if ($sensors->isEmpty()) {
            $this->error('No active sensors found.');

            return self::FAILURE;
        }

        $this->info(
            'Simulating sensor data for '.
            $sensors->count().
            ' active sensors...'
        );

        $this->newLine();

        $failed = false;
        $successful = 0;

        foreach ($sensors as $sensor) {
            /** @var Machine $machine */
            $machine = $sensor->machine;

            $payload = [
                'event_id' => Str::uuid()->toString(),
                'machine_id' => $sensor->machine_id,
                'sensor_id' => $sensor->id,
                'status' => fake()->randomElement(['ON', 'OFF']),
                'temperature' => fake()->randomFloat(2, 50, 95),
                'output' => fake()->numberBetween(80, 150),
                'recorded_at' => now()->format('Y-m-d H:i:s'),
            ];

            $response = Http::post(
                route('api.sensor-data.store'),
                $payload
            );

            if ($response->failed()) {
                $failed = true;

                $this->error(
                    "Failed: {$machine->code} / {$sensor->code}"
                );

                $this->line($response->body());

                continue;
            }

            $successful++;

            $this->info(
                "Sent: {$machine->code} / {$sensor->code}"
            );
        }

        $this->newLine();

        $this->info(
            sprintf(
                '%d of %d sensor transmissions completed successfully.',
                $successful,
                $sensors->count()
            )
        );

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
