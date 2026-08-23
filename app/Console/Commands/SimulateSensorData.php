<?php

namespace App\Console\Commands;

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
        $sensor = Sensor::query()
            ->where('is_active', true)
            ->with('machine')
            ->first();

        if (! $sensor) {
            $this->error('No active sensor found.');

            return self::FAILURE;
        }

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
            $this->error('Failed to send sensor data.');
            $this->line($response->body());

            return self::FAILURE;
        }

        $this->info('Sensor data sent successfully.');

        $this->table(
            ['Field', 'Value'],
            [
                ['Machine', $sensor->machine->code],
                ['Sensor', $sensor->code],
                ['Status', $payload['status']],
                ['Temperature', $payload['temperature']],
                ['Output', $payload['output']],
                ['Recorded At', $payload['recorded_at']],
            ]
        );

        return self::SUCCESS;
    }
}
