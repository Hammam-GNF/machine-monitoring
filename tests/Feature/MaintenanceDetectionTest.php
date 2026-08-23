<?php

use App\Models\Machine;
use App\Models\MaintenanceRecord;
use App\Models\Sensor;

test('three consecutive high temperature readings create maintenance record', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    foreach ([
        ['temperature' => 81, 'recorded_at' => '2026-08-24 10:00:00'],
        ['temperature' => 85, 'recorded_at' => '2026-08-24 10:05:00'],
        ['temperature' => 90, 'recorded_at' => '2026-08-24 10:10:00'],
    ] as $index => $reading) {
        $this->postJson(route('api.sensor-data.store'), [
            'event_id' => "550e8400-e29b-41d4-a716-44665544000{$index}",
            'machine_id' => $machine->id,
            'sensor_id' => $sensor->id,
            'status' => 'ON',
            'temperature' => $reading['temperature'],
            'output' => 100,
            'recorded_at' => $reading['recorded_at'],
        ])->assertSuccessful();
    }

    $this->assertDatabaseHas('maintenance_records', [
        'machine_id' => $machine->id,
        'reason' => 'HIGH_TEMPERATURE',
        'status' => 'open',
        'detected_at' => '2026-08-24 10:10:00',
    ]);
});

test('two high temperature readings do not create maintenance record', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    foreach ([
        ['temperature' => 85, 'recorded_at' => '2026-08-24 10:00:00'],
        ['temperature' => 90, 'recorded_at' => '2026-08-24 10:05:00'],
    ] as $index => $reading) {
        $this->postJson(route('api.sensor-data.store'), [
            'event_id' => "550e8400-e29b-41d4-a716-44665544001{$index}",
            'machine_id' => $machine->id,
            'sensor_id' => $sensor->id,
            'status' => 'ON',
            'temperature' => $reading['temperature'],
            'output' => 100,
            'recorded_at' => $reading['recorded_at'],
        ])->assertSuccessful();
    }

    expect(
        MaintenanceRecord::where('machine_id', $machine->id)->count()
    )->toBe(0);
});

test('interrupted high temperature readings do not create maintenance record', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    foreach ([
        ['temperature' => 85, 'recorded_at' => '2026-08-24 10:00:00'],
        ['temperature' => 75, 'recorded_at' => '2026-08-24 10:05:00'],
        ['temperature' => 90, 'recorded_at' => '2026-08-24 10:10:00'],
    ] as $index => $reading) {
        $this->postJson(route('api.sensor-data.store'), [
            'event_id' => "550e8400-e29b-41d4-a716-44665544002{$index}",
            'machine_id' => $machine->id,
            'sensor_id' => $sensor->id,
            'status' => 'ON',
            'temperature' => $reading['temperature'],
            'output' => 100,
            'recorded_at' => $reading['recorded_at'],
        ])->assertSuccessful();
    }

    expect(
        MaintenanceRecord::where('machine_id', $machine->id)->count()
    )->toBe(0);
});

test('existing open maintenance is not duplicated', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    foreach ([
        ['temperature' => 85, 'recorded_at' => '2026-08-24 10:00:00'],
        ['temperature' => 87, 'recorded_at' => '2026-08-24 10:05:00'],
        ['temperature' => 90, 'recorded_at' => '2026-08-24 10:10:00'],
        ['temperature' => 91, 'recorded_at' => '2026-08-24 10:15:00'],
    ] as $index => $reading) {
        $this->postJson(route('api.sensor-data.store'), [
            'event_id' => "550e8400-e29b-41d4-a716-44665544003{$index}",
            'machine_id' => $machine->id,
            'sensor_id' => $sensor->id,
            'status' => 'ON',
            'temperature' => $reading['temperature'],
            'output' => 100,
            'recorded_at' => $reading['recorded_at'],
        ])->assertSuccessful();
    }

    expect(
        MaintenanceRecord::where('machine_id', $machine->id)
            ->where('reason', 'HIGH_TEMPERATURE')
            ->count()
    )->toBe(1);
});

test('machine off for thirty minutes does not create maintenance', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    foreach ([
        [
            'status' => 'ON',
            'recorded_at' => '2026-08-24 10:00:00',
        ],
        [
            'status' => 'OFF',
            'recorded_at' => '2026-08-24 10:30:00',
        ],
    ] as $index => $reading) {
        $this->postJson(route('api.sensor-data.store'), [
            'event_id' => "550e8400-e29b-41d4-a716-44665544004{$index}",
            'machine_id' => $machine->id,
            'sensor_id' => $sensor->id,
            'status' => $reading['status'],
            'temperature' => 70,
            'output' => 100,
            'recorded_at' => $reading['recorded_at'],
        ])->assertSuccessful();
    }

    expect(
        MaintenanceRecord::where('machine_id', $machine->id)->count()
    )->toBe(0);
});

test('machine off for more than thirty minutes creates maintenance', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    foreach ([
        [
            'status' => 'ON',
            'recorded_at' => '2026-08-24 10:00:00',
        ],
        [
            'status' => 'OFF',
            'recorded_at' => '2026-08-24 10:15:00',
        ],
        [
            'status' => 'OFF',
            'recorded_at' => '2026-08-24 10:46:00',
        ],
    ] as $index => $reading) {
        $this->postJson(route('api.sensor-data.store'), [
            'event_id' => "550e8400-e29b-41d4-a716-44665544005{$index}",
            'machine_id' => $machine->id,
            'sensor_id' => $sensor->id,
            'status' => $reading['status'],
            'temperature' => 70,
            'output' => 100,
            'recorded_at' => $reading['recorded_at'],
        ])->assertSuccessful();
    }

    $this->assertDatabaseHas('maintenance_records', [
        'machine_id' => $machine->id,
        'reason' => 'MACHINE_OFF',
        'status' => 'open',
        'detected_at' => '2026-08-24 10:46:00',
    ]);
});

test('machine turning on resets the off duration', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    foreach ([
        ['status' => 'ON', 'recorded_at' => '2026-08-24 10:00:00'],
        ['status' => 'OFF', 'recorded_at' => '2026-08-24 10:15:00'],
        ['status' => 'ON', 'recorded_at' => '2026-08-24 10:30:00'],
        ['status' => 'OFF', 'recorded_at' => '2026-08-24 10:50:00'],
    ] as $index => $reading) {
        $this->postJson(route('api.sensor-data.store'), [
            'event_id' => "550e8400-e29b-41d4-a716-44665544006{$index}",
            'machine_id' => $machine->id,
            'sensor_id' => $sensor->id,
            'status' => $reading['status'],
            'temperature' => 70,
            'output' => 100,
            'recorded_at' => $reading['recorded_at'],
        ])->assertSuccessful();
    }

    expect(
        MaintenanceRecord::where('machine_id', $machine->id)->count()
    )->toBe(0);
});
