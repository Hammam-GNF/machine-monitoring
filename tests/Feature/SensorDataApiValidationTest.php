<?php

use App\Models\Machine;
use App\Models\Sensor;

test('sensor data can be submitted with valid payload', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    $this->postJson(route('api.sensor-data.store'), [
        'event_id' => '550e8400-e29b-41d4-a716-446655440000',
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'status' => 'ON',
        'temperature' => 72.50,
        'output' => 120,
        'recorded_at' => '2026-08-23 20:00:00',
    ])
        ->assertCreated()
        ->assertJsonPath('data.event_id', '550e8400-e29b-41d4-a716-446655440000');
});

test('sensor data requires required fields', function () {
    $this->postJson(route('api.sensor-data.store'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'event_id',
            'machine_id',
            'sensor_id',
            'status',
            'output',
            'recorded_at',
        ]);
});

test('event id must be a valid uuid', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    $this->postJson(route('api.sensor-data.store'), [
        'event_id' => 'invalid-event-id',
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'status' => 'ON',
        'temperature' => 72.50,
        'output' => 120,
        'recorded_at' => '2026-08-23 20:00:00',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('event_id');
});

test('status must be ON or OFF', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    $this->postJson(route('api.sensor-data.store'), [
        'event_id' => '550e8400-e29b-41d4-a716-446655440001',
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'status' => 'INVALID',
        'temperature' => 72.50,
        'output' => 120,
        'recorded_at' => '2026-08-23 20:00:00',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

test('output must be a non negative integer', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    $this->postJson(route('api.sensor-data.store'), [
        'event_id' => '550e8400-e29b-41d4-a716-446655440002',
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'status' => 'ON',
        'temperature' => 72.50,
        'output' => -1,
        'recorded_at' => '2026-08-23 20:00:00',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('output');
});

test('sensor must belong to the selected machine', function () {
    $machine = Machine::factory()->create();
    $anotherMachine = Machine::factory()->create();

    $sensor = Sensor::factory()->create([
        'machine_id' => $anotherMachine->id,
    ]);

    $this->postJson(route('api.sensor-data.store'), [
        'event_id' => '550e8400-e29b-41d4-a716-446655440003',
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'status' => 'ON',
        'temperature' => 72.50,
        'output' => 120,
        'recorded_at' => '2026-08-23 20:00:00',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('sensor_id');
});
