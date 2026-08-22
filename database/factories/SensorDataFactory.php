<?php

namespace Database\Factories;

use App\Models\Machine;
use App\Models\Sensor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SensorData>
 */
class SensorDataFactory extends Factory
{
    public function definition(): array
    {
        $recordedAt = fake()->dateTimeBetween('-30 days', 'now');

        return [
            'event_id' => Str::uuid()->toString(),
            'machine_id' => Machine::factory(),
            'sensor_id' => Sensor::factory(),
            'status' => fake()->randomElement(['ON', 'OFF']),
            'temperature' => fake()->randomFloat(2, 50, 95),
            'output' => fake()->numberBetween(80, 150),
            'recorded_at' => $recordedAt,
            'received_at' => fake()->dateTimeBetween($recordedAt, 'now'),
            'created_at' => $recordedAt,
        ];
    }
}
