<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\MaintenanceRecord;
use App\Models\Sensor;
use App\Models\SensorData;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'System Administrator',
            'email' => 'admin@machine-monitoring.com',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Monitoring Viewer',
            'email' => 'viewer@machine-monitoring.com',
            'role' => 'viewer',
        ]);

        $machines = Machine::factory()
            ->count(10)
            ->create();

        foreach ($machines as $machine) {
            $machine->sensors()->createMany(
                collect(range(1, 3))
                    ->map(fn () => [
                        'code' => 'SNS-'.Str::upper(Str::random(6)),
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
                    ])
                    ->all()
            );
        }

        /** @var Collection<int, Sensor> $sensors */
        $sensors = Sensor::query()
            ->whereIn('machine_id', $machines->modelKeys())
            ->get();

        SensorData::factory()
            ->count(1000)
            ->state(function () use ($sensors) {
                $sensor = $sensors->random();
                $recordedAt = fake()->dateTimeBetween('-30 days', 'now');

                return [
                    'event_id' => Str::uuid()->toString(),
                    'machine_id' => $sensor->machine_id,
                    'sensor_id' => $sensor->id,
                    'status' => fake()->randomElement(['ON', 'OFF']),
                    'temperature' => fake()->randomFloat(2, 50, 95),
                    'output' => fake()->numberBetween(80, 150),
                    'recorded_at' => $recordedAt,
                    'received_at' => fake()->dateTimeBetween($recordedAt, 'now'),
                    'created_at' => $recordedAt,
                ];
            })
            ->create();

        MaintenanceRecord::factory()
            ->count(15)
            ->state(function () use ($machines) {
                $detectedAt = fake()->dateTimeBetween('-30 days', 'now');
                $status = fake()->randomElement(['open', 'resolved']);

                return [
                    'machine_id' => $machines->random()->id,
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
            })
            ->create();
    }
}
