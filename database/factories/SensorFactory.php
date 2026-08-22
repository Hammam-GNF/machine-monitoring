<?php

namespace Database\Factories;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sensor>
 */
class SensorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'machine_id' => Machine::factory(),
            'code' => 'SNS-' . fake()->numerify('####'),
            'name' => fake()->randomElement([
                'Temperature Sensor',
                'Status Sensor',
                'Production Sensor',
            ]),
            'type' => fake()->randomElement([
                'temperature',
                'status',
                'production',
            ]),
            'is_active' => true,
        ];
    }
}
