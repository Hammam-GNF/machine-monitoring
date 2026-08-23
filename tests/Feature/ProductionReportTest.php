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
