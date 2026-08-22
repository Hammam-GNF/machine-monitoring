<?php

use App\Models\Machine;
use App\Models\Sensor;
use App\Models\SensorData;

test('sensor belongs to a machine', function () {
    $machine = Machine::factory()->create();

    $sensor = Sensor::factory()
        ->for($machine)
        ->create();

    expect($sensor->machine)
        ->toBeInstanceOf(Machine::class)
        ->and($sensor->machine->is($machine))->toBeTrue();
});

test('sensor has many sensor data', function () {
    $sensor = Sensor::factory()
        ->has(SensorData::factory()->count(5))
        ->create();

    expect($sensor->sensorData)
        ->toHaveCount(5)
        ->each->toBeInstanceOf(SensorData::class);
});
