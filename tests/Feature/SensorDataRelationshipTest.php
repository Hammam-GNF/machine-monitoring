<?php

use App\Models\Machine;
use App\Models\Sensor;
use App\Models\SensorData;

test('sensor data belongs to a machine', function () {
    $machine = Machine::factory()->create();

    $sensor = Sensor::factory()
        ->for($machine)
        ->create();

    $sensorData = SensorData::factory()
        ->for($machine)
        ->for($sensor)
        ->create();

    expect($sensorData->machine)
        ->toBeInstanceOf(Machine::class)
        ->and($sensorData->machine->is($machine))->toBeTrue();
});

test('sensor data belongs to a sensor', function () {
    $sensor = Sensor::factory()->create();

    $sensorData = SensorData::factory()
        ->for($sensor)
        ->for($sensor->machine)
        ->create();

    expect($sensorData->sensor)
        ->toBeInstanceOf(Sensor::class)
        ->and($sensorData->sensor->is($sensor))->toBeTrue();
});
