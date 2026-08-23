<?php

use App\Models\Machine;
use App\Models\Sensor;
use Illuminate\Support\Facades\Http;

test('sensor simulator sends sensor data successfully', function () {
    $machine = Machine::factory()->create([
        'is_active' => true,
    ]);

    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
        'is_active' => true,
    ]);

    Http::fake();

    $this->artisan('app:simulate-sensor-data')
        ->assertSuccessful();

    Http::assertSent(function ($request) use ($machine, $sensor) {
        return $request->url() === route('api.sensor-data.store')
            && $request['machine_id'] === $machine->id
            && $request['sensor_id'] === $sensor->id
            && in_array($request['status'], ['ON', 'OFF'], true)
            && is_numeric($request['temperature'])
            && $request['output'] >= 80
            && $request['output'] <= 150
            && preg_match(
                '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
                $request['event_id']
            );
    });
});

test('sensor simulator fails when no active sensor exists', function () {
    $this->artisan('app:simulate-sensor-data')
        ->assertFailed();
});

test('sensor simulator only selects active sensors', function () {
    $machine = Machine::factory()->create([
        'is_active' => true,
    ]);

    Sensor::factory()->create([
        'machine_id' => $machine->id,
        'is_active' => false,
    ]);

    Http::fake();

    $this->artisan('app:simulate-sensor-data')
        ->assertFailed();

    Http::assertNothingSent();
});
