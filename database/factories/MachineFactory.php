<?php

namespace Database\Factories;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Machine>
 */
class MachineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'MC-'.fake()->unique()->numerify('###'),
            'name' => fake()->randomElement([
                'Production Machine',
                'Assembly Machine',
                'Packaging Machine',
                'Processing Machine',
            ]),
            'location' => fake()->randomElement([
                'Line A',
                'Line B',
                'Line C',
                'Line D',
            ]),
            'machine_type' => fake()->randomElement([
                'CNC',
                'Press',
                'Assembly',
                'Packaging',
            ]),
            'installed_at' => fake()->dateTimeBetween('-5 years', '-1 year')->format('Y-m-d'),
            'is_active' => true,
        ];
    }
}
