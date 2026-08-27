<?php

test('iot simulator has configured devices', function () {
    $devices = json_decode(
        file_get_contents(base_path('device-simulator/devices.json')),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    expect($devices)
        ->not->toBeEmpty();

    foreach ($devices as $device) {
        expect($device)
            ->toHaveKeys([
                'machine_id',
                'sensor_id',
            ])
            ->and($device['machine_id'])->toBeInt()
            ->and($device['sensor_id'])->toBeInt();
    }
});

test('iot simulator has multiple devices across multiple machines', function () {
    $devices = json_decode(
        file_get_contents(base_path('device-simulator/devices.json')),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    $machineIds = array_unique(
        array_column($devices, 'machine_id')
    );

    expect($devices)->toHaveCount(30)
        ->and($machineIds)->toHaveCount(10);
});

test('each machine has multiple configured sensors', function () {
    $devices = json_decode(
        file_get_contents(base_path('device-simulator/devices.json')),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    $sensorsPerMachine = [];

    foreach ($devices as $device) {
        $sensorsPerMachine[$device['machine_id']][] = $device['sensor_id'];
    }

    expect($sensorsPerMachine)->toHaveCount(10);

    foreach ($sensorsPerMachine as $sensorIds) {
        expect($sensorIds)->toHaveCount(3)
            ->and(array_unique($sensorIds))->toHaveCount(3);
    }
});
