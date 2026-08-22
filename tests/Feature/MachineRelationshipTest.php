<?php

use App\Models\Machine;
use App\Models\MaintenanceRecord;
use App\Models\Sensor;
use App\Models\SensorData;

test('machine has many sensors', function () {
    $machine = Machine::factory()
        ->has(Sensor::factory()->count(3))
        ->create();

    expect($machine->sensors)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Sensor::class);
});

test('machine has many sensor data', function () {
    $machine = Machine::factory()
        ->has(SensorData::factory()->count(5))
        ->create();

    expect($machine->sensorData)
        ->toHaveCount(5)
        ->each->toBeInstanceOf(SensorData::class);
});

test('machine has many maintenance records', function () {
    $machine = Machine::factory()
        ->has(MaintenanceRecord::factory()->count(2))
        ->create();

    expect($machine->maintenanceRecords)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(MaintenanceRecord::class);
});
