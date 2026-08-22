<?php

namespace Database\Factories;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaintenanceRecord>
 */
class MaintenanceRecordFactory extends Factory
{
    public function definition(): array
    {
        $detectedAt = fake()->dateTimeBetween('-30 days', 'now');
        $status = fake()->randomElement(['open', 'resolved']);

        return [
            'machine_id' => Machine::factory(),
            'reason' => fake()->randomElement([
                'HIGH_TEMPERATURE',
                'MACHINE_OFF',
            ]),
            'detected_at' => $detectedAt,
            'resolved_at' => $status === 'resolved'
                ? fake()->dateTimeBetween($detectedAt, 'now')
                : null,
            'status' => $status,
        ];
    }
}
