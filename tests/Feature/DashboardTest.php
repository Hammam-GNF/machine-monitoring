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

test('dashboard can search machines by code or name', function () {
    $user = User::factory()->create();

    Machine::factory()->create([
        'code' => 'MC-001',
        'name' => 'Production Machine',
    ]);

    Machine::factory()->create([
        'code' => 'MC-002',
        'name' => 'Packaging Machine',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard', [
        'search' => 'MC-001',
    ]));

    $response
        ->assertOk()
        ->assertSee('MC-001')
        ->assertDontSee('MC-002');
});

test('dashboard can filter machines by sensor status', function () {
    $user = User::factory()->create();

    $onMachine = Machine::factory()->create([
        'code' => 'MC-ON',
        'is_active' => true,
    ]);

    $onSensor = Sensor::factory()->create([
        'machine_id' => $onMachine->id,
        'is_active' => true,
    ]);

    SensorData::factory()->create([
        'machine_id' => $onMachine->id,
        'sensor_id' => $onSensor->id,
        'status' => 'ON',
        'recorded_at' => now(),
    ]);

    $offMachine = Machine::factory()->create([
        'code' => 'MC-OFF',
        'is_active' => true,
    ]);

    $offSensor = Sensor::factory()->create([
        'machine_id' => $offMachine->id,
        'is_active' => true,
    ]);

    SensorData::factory()->create([
        'machine_id' => $offMachine->id,
        'sensor_id' => $offSensor->id,
        'status' => 'OFF',
        'recorded_at' => now(),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard', [
        'status' => 'ON',
    ]));

    $response
        ->assertOk()
        ->assertSee('MC-ON')
        ->assertDontSee('MC-OFF');
});

test('dashboard can filter inactive machines', function () {
    $user = User::factory()->create();

    Machine::factory()->create([
        'code' => 'MC-ACTIVE',
        'is_active' => true,
    ]);

    Machine::factory()->create([
        'code' => 'MC-INACTIVE',
        'is_active' => false,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard', [
        'status' => 'inactive',
    ]));

    $response
        ->assertOk()
        ->assertSee('MC-INACTIVE')
        ->assertDontSee('MC-ACTIVE');
});

test('dashboard can filter machines needing maintenance', function () {
    $user = User::factory()->create();

    $machineWithMaintenance = Machine::factory()->create([
        'code' => 'MC-MAINT',
    ]);

    MaintenanceRecord::factory()->create([
        'machine_id' => $machineWithMaintenance->id,
        'status' => 'open',
        'detected_at' => now(),
    ]);

    $normalMachine = Machine::factory()->create([
        'code' => 'MC-NORMAL',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard', [
        'maintenance' => 'needs_maintenance',
    ]));

    $response
        ->assertOk()
        ->assertSee('MC-MAINT')
        ->assertDontSee('MC-NORMAL');
});

test('dashboard can filter machines without open maintenance', function () {
    $user = User::factory()->create();

    $machineWithMaintenance = Machine::factory()->create([
        'code' => 'MC-MAINT',
    ]);

    MaintenanceRecord::factory()->create([
        'machine_id' => $machineWithMaintenance->id,
        'status' => 'open',
        'detected_at' => now(),
    ]);

    $normalMachine = Machine::factory()->create([
        'code' => 'MC-NORMAL',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard', [
        'maintenance' => 'normal',
    ]));

    $response
        ->assertOk()
        ->assertSee('MC-NORMAL')
        ->assertDontSee('MC-MAINT');
});

test('dashboard preserves filters in the rendered view', function () {
    $user = User::factory()->create();

    Machine::factory()->create([
        'code' => 'MC-ON',
        'name' => 'Production Machine',
        'is_active' => true,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard', [
        'search' => 'MC-ON',
        'status' => 'ON',
        'maintenance' => 'normal',
    ]));

    $response
        ->assertOk()
        ->assertViewHas('search', 'MC-ON')
        ->assertViewHas('status', 'ON')
        ->assertViewHas('maintenance', 'normal');
});

test('dashboard returns latest sensor data on subsequent requests', function () {
    $user = User::factory()->create();

    $machine = Machine::factory()->create([
        'code' => 'MC-001',
        'is_active' => true,
    ]);

    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
        'is_active' => true,
    ]);

    SensorData::factory()->create([
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'status' => 'ON',
        'temperature' => 70,
        'recorded_at' => now()->subSeconds(10),
    ]);

    $this->actingAs($user);

    $firstResponse = $this->get(route('dashboard'));

    $firstResponse
        ->assertOk()
        ->assertSee('70.00 °C');

    SensorData::factory()->create([
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'status' => 'OFF',
        'temperature' => 85,
        'recorded_at' => now(),
    ]);

    $secondResponse = $this->get(route('dashboard'));

    $secondResponse
        ->assertOk()
        ->assertSee('85.00 °C')
        ->assertSee('OFF');
});

test('dashboard provides empty filter values by default', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertViewHas('search', '')
        ->assertViewHas('status', '')
        ->assertViewHas('maintenance', '');
});

test('dashboard polling returns current machine data', function () {
    $user = User::factory()->create();

    $machine = Machine::factory()->create([
        'code' => 'MC-001',
        'name' => 'Production Machine',
        'is_active' => true,
    ]);

    $sensor = Sensor::factory()->create([
        'machine_id' => $machine->id,
        'is_active' => true,
    ]);

    SensorData::factory()->create([
        'machine_id' => $machine->id,
        'sensor_id' => $sensor->id,
        'status' => 'ON',
        'temperature' => 85.5,
        'output' => 100,
        'recorded_at' => now(),
    ]);

    $this->actingAs($user);

    $response = $this->getJson(route('dashboard.data'));

    $response
        ->assertOk()
        ->assertJsonPath('stats.total', 1)
        ->assertJsonPath('stats.active', 1)
        ->assertJsonPath('machines.0.code', 'MC-001')
        ->assertJsonPath('machines.0.status', 'ON')
        ->assertJsonPath('machines.0.temperature', 85.5);
});
