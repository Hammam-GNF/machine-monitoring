<?php

use App\Models\Machine;
use App\Models\Sensor;
use App\Models\SensorData;
use App\Services\ProductionReportService;

function createProductionSensor(): array
{
    $machine = Machine::factory()->create();

    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
    ]);

    return [$machine, $sensor];
}

function createProductionReading(
    Machine $machine,
    Sensor $sensor,
    int $output,
    string $recordedAt,
    string $status = 'ON'
): void {
    SensorData::factory()->create([
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'output' => $output,
        'status' => $status,
        'recorded_at' => $recordedAt,
    ]);
}

test('production can be aggregated by day', function () {
    [$machine, $sensor] = createProductionSensor();

    createProductionReading($machine, $sensor, 120, '2026-08-24 10:00:00');
    createProductionReading($machine, $sensor, 115, '2026-08-24 10:05:00');
    createProductionReading($machine, $sensor, 125, '2026-08-24 10:10:00');

    $report = app(ProductionReportService::class)->aggregateByDay();

    expect($report)->toHaveCount(1)
        ->and($report->items()[0]->date)->toBe('2026-08-24')
        ->and((int) $report->items()[0]->total_output)->toBe(360);
});

test('production can be aggregated by month', function () {
    [$machine, $sensor] = createProductionSensor();

    createProductionReading($machine, $sensor, 100, '2026-08-01 10:00:00');
    createProductionReading($machine, $sensor, 200, '2026-08-15 10:00:00');
    createProductionReading($machine, $sensor, 300, '2026-08-24 10:00:00');

    $report = app(ProductionReportService::class)->aggregateByMonth();

    expect($report)->toHaveCount(1)
        ->and((int) $report->items()[0]->year)->toBe(2026)
        ->and((int) $report->items()[0]->month)->toBe(8)
        ->and((int) $report->items()[0]->total_output)->toBe(600);
});

test('production aggregation separates different days', function () {
    [$machine, $sensor] = createProductionSensor();

    createProductionReading($machine, $sensor, 100, '2026-08-24 23:55:00');
    createProductionReading($machine, $sensor, 200, '2026-08-25 00:05:00');

    $report = app(ProductionReportService::class)->aggregateByDay();

    expect($report)->toHaveCount(2)
        ->and((int) $report->items()[0]->total_output)->toBe(100)
        ->and((int) $report->items()[1]->total_output)->toBe(200);
});

test('production aggregation separates different machines', function () {
    [$machineOne, $sensorOne] = createProductionSensor();
    [$machineTwo, $sensorTwo] = createProductionSensor();

    createProductionReading(
        $machineOne,
        $sensorOne,
        100,
        '2026-08-24 10:00:00'
    );

    createProductionReading(
        $machineTwo,
        $sensorTwo,
        500,
        '2026-08-24 10:00:00'
    );

    $report = app(ProductionReportService::class)->aggregateByDay();

    expect($report)->toHaveCount(2)
        ->and((int) $report->items()[0]->total_output)->toBe(100)
        ->and((int) $report->items()[1]->total_output)->toBe(500);
});

test('production can be filtered by date range', function () {
    [$machine, $sensor] = createProductionSensor();

    createProductionReading($machine, $sensor, 100, '2026-08-23 10:00:00');
    createProductionReading($machine, $sensor, 200, '2026-08-24 10:00:00');
    createProductionReading($machine, $sensor, 300, '2026-08-25 10:00:00');

    $report = app(ProductionReportService::class)->aggregateByDay(
        dateFrom: '2026-08-24',
        dateTo: '2026-08-24'
    );

    expect($report)->toHaveCount(1)
        ->and($report->items()[0]->date)->toBe('2026-08-24')
        ->and((int) $report->items()[0]->total_output)->toBe(200);
});

test('production can be filtered by machine', function () {
    [$machineOne, $sensorOne] = createProductionSensor();
    [$machineTwo, $sensorTwo] = createProductionSensor();

    createProductionReading(
        $machineOne,
        $sensorOne,
        100,
        '2026-08-24 10:00:00'
    );

    createProductionReading(
        $machineTwo,
        $sensorTwo,
        500,
        '2026-08-24 10:00:00'
    );

    $report = app(ProductionReportService::class)->aggregateByDay(
        machineId: $machineOne->id
    );

    expect($report)->toHaveCount(1)
        ->and($report->items()[0]->machine_id)->toBe($machineOne->id)
        ->and((int) $report->items()[0]->total_output)->toBe(100);
});

test('production can be filtered by shift one', function () {
    [$machine, $sensor] = createProductionSensor();

    createProductionReading($machine, $sensor, 100, '2026-08-24 05:59:00');
    createProductionReading($machine, $sensor, 200, '2026-08-24 06:00:00');
    createProductionReading($machine, $sensor, 300, '2026-08-24 10:00:00');
    createProductionReading($machine, $sensor, 400, '2026-08-24 13:59:59');
    createProductionReading($machine, $sensor, 500, '2026-08-24 14:00:00');

    $report = app(ProductionReportService::class)->aggregateByDay(
        dateFrom: '2026-08-24',
        dateTo: '2026-08-24',
        shift: 1
    );

    expect($report)->toHaveCount(1)
        ->and((int) $report->items()[0]->total_output)->toBe(900);
});

test('production can be filtered by shift two', function () {
    [$machine, $sensor] = createProductionSensor();

    createProductionReading($machine, $sensor, 100, '2026-08-24 13:59:59');
    createProductionReading($machine, $sensor, 200, '2026-08-24 14:00:00');
    createProductionReading($machine, $sensor, 300, '2026-08-24 18:00:00');
    createProductionReading($machine, $sensor, 400, '2026-08-24 21:59:59');
    createProductionReading($machine, $sensor, 500, '2026-08-24 22:00:00');

    $report = app(ProductionReportService::class)->aggregateByDay(
        dateFrom: '2026-08-24',
        dateTo: '2026-08-24',
        shift: 2
    );

    expect($report)->toHaveCount(1)
        ->and((int) $report->items()[0]->total_output)->toBe(900);
});

test('production can be filtered by shift three', function () {
    [$machine, $sensor] = createProductionSensor();

    createProductionReading($machine, $sensor, 100, '2026-08-24 05:59:59');
    createProductionReading($machine, $sensor, 200, '2026-08-24 06:00:00');
    createProductionReading($machine, $sensor, 300, '2026-08-24 21:59:59');
    createProductionReading($machine, $sensor, 400, '2026-08-24 22:00:00');
    createProductionReading($machine, $sensor, 500, '2026-08-24 23:00:00');

    $report = app(ProductionReportService::class)->aggregateByDay(
        dateFrom: '2026-08-24',
        dateTo: '2026-08-24',
        shift: 3
    );

    expect($report)->toHaveCount(1)
        ->and((int) $report->items()[0]->total_output)->toBe(1000);
});

test('invalid shift is rejected', function () {
    expect(
        fn () => app(ProductionReportService::class)
            ->aggregateByDay(shift: 4)
    )->toThrow(InvalidArgumentException::class);
});

test('production metrics can be calculated', function () {
    [$machine, $sensor] = createProductionSensor();

    createProductionReading(
        $machine,
        $sensor,
        100,
        '2026-08-24 06:00:00',
        'ON'
    );

    createProductionReading(
        $machine,
        $sensor,
        200,
        '2026-08-24 07:00:00',
        'ON'
    );

    createProductionReading(
        $machine,
        $sensor,
        300,
        '2026-08-24 08:00:00',
        'OFF'
    );

    createProductionReading(
        $machine,
        $sensor,
        400,
        '2026-08-24 09:00:00',
        'ON'
    );

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

test('production metrics respect shift filter', function () {
    [$machine, $sensor] = createProductionSensor();

    createProductionReading(
        $machine,
        $sensor,
        100,
        '2026-08-24 05:00:00',
        'OFF'
    );

    createProductionReading(
        $machine,
        $sensor,
        200,
        '2026-08-24 06:00:00',
        'ON'
    );

    createProductionReading(
        $machine,
        $sensor,
        300,
        '2026-08-24 10:00:00',
        'ON'
    );

    createProductionReading(
        $machine,
        $sensor,
        400,
        '2026-08-24 14:00:00',
        'OFF'
    );

    $metrics = app(ProductionReportService::class)->metrics(
        dateFrom: '2026-08-24',
        dateTo: '2026-08-24',
        shift: 1,
        machineId: $machine->id
    );

    expect($metrics['total_output'])->toBe(500)
        ->and($metrics['uptime_percentage'])->toBe(100.0)
        ->and($metrics['downtime_percentage'])->toBe(0.0);
});

test('production metrics can be filtered by machine', function () {
    [$machineOne, $sensorOne] = createProductionSensor();
    [$machineTwo, $sensorTwo] = createProductionSensor();

    createProductionReading(
        $machineOne,
        $sensorOne,
        100,
        '2026-08-24 10:00:00'
    );

    createProductionReading(
        $machineTwo,
        $sensorTwo,
        500,
        '2026-08-24 10:00:00'
    );

    $metrics = app(ProductionReportService::class)->metrics(
        dateFrom: '2026-08-24',
        dateTo: '2026-08-24',
        machineId: $machineOne->id
    );

    expect($metrics['total_output'])->toBe(100)
        ->and($metrics['uptime_percentage'])->toBe(100.0);
});

test('production metrics return zero when no data matches filters', function () {
    [$machine, $sensor] = createProductionSensor();

    $metrics = app(ProductionReportService::class)->metrics(
        dateFrom: '2026-08-24',
        dateTo: '2026-08-24',
        machineId: $machine->id
    );

    expect($metrics)->toBe([
        'total_output' => 0,
        'average_output_per_hour' => 0.0,
        'uptime_percentage' => 0.0,
        'downtime_percentage' => 0.0,
    ]);
});
