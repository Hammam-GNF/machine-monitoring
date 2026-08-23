<?php

use App\Models\Machine;
use App\Models\MaintenanceRecord;
use App\Models\Sensor;
use App\Models\SensorData;
use App\Models\User;
use Illuminate\Support\Str;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertViewIs('dashboard');
});

test('dashboard displays current machine sensor data', function () {
    $user = User::factory()->create();

    $machine = Machine::factory()->create([
        'code' => 'MC-001',
        'name' => 'Production Machine',
        'location' => 'Line 1',
        'is_active' => true,
    ]);

    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
        'is_active' => true,
    ]);

    SensorData::factory()->create([
        'event_id' => Str::uuid()->toString(),
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'status' => 'ON',
        'temperature' => 91.40,
        'output' => 120,
        'recorded_at' => now(),
        'received_at' => now(),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertSee('MC-001')
        ->assertSee('Production Machine')
        ->assertSee('Line 1')
        ->assertSee('ON')
        ->assertSee('91.40 °C');
});

test('dashboard displays maintenance indicator for machines with open maintenance', function () {
    $user = User::factory()->create();

    $machine = Machine::factory()->create([
        'is_active' => true,
    ]);

    MaintenanceRecord::factory()->create([
        'machine_id' => $machine->id,
        'reason' => 'HIGH_TEMPERATURE',
        'detected_at' => now(),
        'status' => 'open',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertSee('Needs Maintenance');
});

test('dashboard handles machine without sensor data', function () {
    $user = User::factory()->create();

    $machine = Machine::factory()->create([
        'code' => 'MC-002',
        'name' => 'Machine Without Data',
        'is_active' => true,
    ]);

    Sensor::factory()->create([
        'machine_id' => $machine->id,
        'is_active' => true,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertSee('MC-002')
        ->assertSee('Machine Without Data')
        ->assertSee('No Data');
});
