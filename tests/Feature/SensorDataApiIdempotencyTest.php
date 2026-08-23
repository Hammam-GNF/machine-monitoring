<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\Sensor;
use App\Models\SensorData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensorDataApiIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_event_id_does_not_create_another_sensor_data(): void
    {
        $machine = Machine::factory()->create();
        $sensor = Sensor::factory()->create([
            'machine_id' => $machine->id,
        ]);

        $payload = [
            'event_id' => '550e8400-e29b-41d4-a716-446655440000',
            'machine_id' => $machine->id,
            'sensor_id' => $sensor->id,
            'status' => 'ON',
            'temperature' => 72.50,
            'output' => 120,
            'recorded_at' => '2026-08-23 20:00:00',
        ];

        $this->postJson(route('api.sensor-data.store'), $payload)
            ->assertCreated();

        $this->postJson(route('api.sensor-data.store'), [
            ...$payload,
            'temperature' => 75.00,
            'output' => 150,
        ])
            ->assertOk()
            ->assertJsonPath(
                'message',
                'Sensor data already received.'
            );

        $this->assertDatabaseCount('sensor_data', 1);

        $this->assertDatabaseHas('sensor_data', [
            'event_id' => '550e8400-e29b-41d4-a716-446655440000',
            'temperature' => 72.50,
            'output' => 120,
        ]);
    }

    public function test_duplicate_event_id_returns_existing_sensor_data(): void
    {
        $machine = Machine::factory()->create();
        $sensor = Sensor::factory()->create([
            'machine_id' => $machine->id,
        ]);

        $sensorData = SensorData::factory()->create([
            'machine_id' => $machine->id,
            'sensor_id' => $sensor->id,
            'event_id' => '660e8400-e29b-41d4-a716-446655440001',
        ]);

        $response = $this->postJson(
            route('api.sensor-data.store'),
            [
                'event_id' => '660e8400-e29b-41d4-a716-446655440001',
                'machine_id' => $machine->id,
                'sensor_id' => $sensor->id,
                'status' => 'OFF',
                'temperature' => 80.00,
                'output' => 200,
                'recorded_at' => '2026-08-23 21:00:00',
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.id',
                $sensorData->id
            );

        $this->assertDatabaseCount('sensor_data', 1);
    }
}
