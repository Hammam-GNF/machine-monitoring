<?php

use App\Models\Machine;
use App\Models\Sensor;
use App\Models\SensorData;
use App\Services\ProductionReportService;

test('production can be aggregated by day', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    SensorData::factory()->create([
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'output' => 120,
        'recorded_at' => '2026-08-24 10:00:00',
    ]);

    SensorData::factory()->create([
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'output' => 115,
        'recorded_at' => '2026-08-24 10:05:00',
    ]);

    SensorData::factory()->create([
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'output' => 125,
        'recorded_at' => '2026-08-24 10:10:00',
    ]);

    $report = app(ProductionReportService::class)
        ->aggregateByDay();

    expect($report)->toHaveCount(1)
        ->and($report->first()->date)->toBe('2026-08-24')
        ->and((int) $report->first()->total_output)->toBe(360);
});

test('production can be aggregated by month', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    SensorData::factory()->create([
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'output' => 100,
        'recorded_at' => '2026-08-01 10:00:00',
    ]);

    SensorData::factory()->create([
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'output' => 200,
        'recorded_at' => '2026-08-15 10:00:00',
    ]);

    SensorData::factory()->create([
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'output' => 300,
        'recorded_at' => '2026-08-24 10:00:00',
    ]);

    $report = app(ProductionReportService::class)
        ->aggregateByMonth();

    expect($report)->toHaveCount(1)
        ->and((int) $report->first()->year)->toBe(2026)
        ->and((int) $report->first()->month)->toBe(8)
        ->and((int) $report->first()->total_output)->toBe(600);
});

test('production aggregation separates different days', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    SensorData::factory()->create([
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'output' => 100,
        'recorded_at' => '2026-08-24 23:55:00',
    ]);

    SensorData::factory()->create([
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'output' => 200,
        'recorded_at' => '2026-08-25 00:05:00',
    ]);

    $report = app(ProductionReportService::class)
        ->aggregateByDay();

    expect($report)->toHaveCount(2)
        ->and((int) $report[0]->total_output)->toBe(100)
        ->and((int) $report[1]->total_output)->toBe(200);
});

test('production can be filtered by date range', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    SensorData::factory()->create([
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'output' => 100,
        'recorded_at' => '2026-08-23 10:00:00',
    ]);

    SensorData::factory()->create([
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'output' => 200,
        'recorded_at' => '2026-08-24 10:00:00',
    ]);

    SensorData::factory()->create([
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'output' => 300,
        'recorded_at' => '2026-08-25 10:00:00',
    ]);

    $report = app(ProductionReportService::class)
        ->aggregateByDay(
            dateFrom: '2026-08-24',
            dateTo: '2026-08-24'
        );

    expect($report)->toHaveCount(1)
        ->and($report->first()->date)->toBe('2026-08-24')
        ->and((int) $report->first()->total_output)->toBe(200);
});

test('production can be filtered by shift one', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    foreach ([
        ['output' => 100, 'recorded_at' => '2026-08-24 05:59:00'],
        ['output' => 200, 'recorded_at' => '2026-08-24 06:00:00'],
        ['output' => 300, 'recorded_at' => '2026-08-24 10:00:00'],
        ['output' => 400, 'recorded_at' => '2026-08-24 13:59:59'],
        ['output' => 500, 'recorded_at' => '2026-08-24 14:00:00'],
    ] as $reading) {
        SensorData::factory()->create([
            'machine_id' => $machine->id,
            'sensor_id' => $sensor->id,
            'output' => $reading['output'],
            'recorded_at' => $reading['recorded_at'],
        ]);
    }

    $report = app(ProductionReportService::class)
        ->aggregateByDay(
            dateFrom: '2026-08-24',
            dateTo: '2026-08-24',
            shift: 1
        );

    expect($report)->toHaveCount(1)
        ->and((int) $report->first()->total_output)->toBe(900);
});

test('production can be filtered by shift two', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    foreach ([
        ['output' => 100, 'recorded_at' => '2026-08-24 13:59:59'],
        ['output' => 200, 'recorded_at' => '2026-08-24 14:00:00'],
        ['output' => 300, 'recorded_at' => '2026-08-24 18:00:00'],
        ['output' => 400, 'recorded_at' => '2026-08-24 21:59:59'],
        ['output' => 500, 'recorded_at' => '2026-08-24 22:00:00'],
    ] as $reading) {
        SensorData::factory()->create([
            'machine_id' => $machine->id,
            'sensor_id' => $sensor->id,
            'output' => $reading['output'],
            'recorded_at' => $reading['recorded_at'],
        ]);
    }

    $report = app(ProductionReportService::class)
        ->aggregateByDay(
            dateFrom: '2026-08-24',
            dateTo: '2026-08-24',
            shift: 2
        );

    expect($report)->toHaveCount(1)
        ->and((int) $report->first()->total_output)->toBe(900);
});

test('production can be filtered by shift three', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    foreach ([
        ['output' => 100, 'recorded_at' => '2026-08-24 05:59:59'],
        ['output' => 200, 'recorded_at' => '2026-08-24 06:00:00'],
        ['output' => 300, 'recorded_at' => '2026-08-24 21:59:59'],
        ['output' => 400, 'recorded_at' => '2026-08-24 22:00:00'],
        ['output' => 500, 'recorded_at' => '2026-08-24 23:00:00'],
    ] as $reading) {
        SensorData::factory()->create([
            'machine_id' => $machine->id,
            'sensor_id' => $sensor->id,
            'output' => $reading['output'],
            'recorded_at' => $reading['recorded_at'],
        ]);
    }

    $report = app(ProductionReportService::class)
        ->aggregateByDay(
            dateFrom: '2026-08-24',
            dateTo: '2026-08-24',
            shift: 3
        );

    expect($report)->toHaveCount(1)
        ->and((int) $report->first()->total_output)->toBe(1000);
});

test('invalid shift is rejected', function () {
    expect(
        fn () => app(ProductionReportService::class)
            ->aggregateByDay(shift: 4)
    )->toThrow(InvalidArgumentException::class);
});

test('production metrics can be calculated', function () {
    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    foreach ([
        ['output' => 100, 'status' => 'ON', 'recorded_at' => '2026-08-24 06:00:00'],
        ['output' => 200, 'status' => 'ON', 'recorded_at' => '2026-08-24 07:00:00'],
        ['output' => 300, 'status' => 'OFF', 'recorded_at' => '2026-08-24 08:00:00'],
        ['output' => 400, 'status' => 'ON', 'recorded_at' => '2026-08-24 09:00:00'],
    ] as $reading) {
        SensorData::factory()->create([
            'machine_id' => $machine->id,
            'sensor_id' => $sensor->id,
            'output' => $reading['output'],
            'status' => $reading['status'],
            'recorded_at' => $reading['recorded_at'],
        ]);
    }

    $metrics = app(ProductionReportService::class)->metrics(
        dateFrom: '2026-08-24',
        dateTo: '2026-08-24',
        machineId: $machine->id
    );

    expect($metrics['total_output'])->toBe(1000)
        ->and($metrics['average_output_per_hour'])->toBe(250.0)
        ->and($metrics['uptime_percentage'])->toBe(75.0)
        ->and($metrics['downtime_percentage'])->toBe(25.0);
});

test('production metrics can be filtered by machine', function () {
    $machineOne = Machine::factory()->create();
    $machineTwo = Machine::factory()->create();

    $sensorOne = Sensor::factory()->create([
        'machine_id' => $machineOne->id,
    ]);

    $sensorTwo = Sensor::factory()->create([
        'machine_id' => $machineTwo->id,
    ]);

    SensorData::factory()->create([
        'machine_id' => $machineOne->id,
        'sensor_id' => $sensorOne->id,
        'output' => 100,
        'status' => 'ON',
        'recorded_at' => '2026-08-24 10:00:00',
    ]);

    SensorData::factory()->create([
        'machine_id' => $machineTwo->id,
        'sensor_id' => $sensorTwo->id,
        'output' => 500,
        'status' => 'ON',
        'recorded_at' => '2026-08-24 10:00:00',
    ]);

    $metrics = app(ProductionReportService::class)->metrics(
        dateFrom: '2026-08-24',
        dateTo: '2026-08-24',
        machineId: $machineOne->id
    );

    expect($metrics['total_output'])->toBe(100)
        ->and($metrics['uptime_percentage'])->toBe(100.0);
});
