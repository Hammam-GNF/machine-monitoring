<?php

use App\Models\Machine;
use App\Models\Sensor;
use App\Models\User;

test('viewer can view sensor index', function () {
    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    Sensor::factory()->count(3)->create();

    $this->actingAs($viewer)
        ->get(route('sensors.index'))
        ->assertOk()
        ->assertViewIs('sensors.index');
});

test('admin can view sensor index', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    Sensor::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('sensors.index'))
        ->assertOk()
        ->assertViewIs('sensors.index');
});

test('guest cannot access sensor management', function () {
    $this->get(route('sensors.index'))
        ->assertRedirect(route('login'));
});

test('viewer cannot access sensor create page', function () {
    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    $this->actingAs($viewer)
        ->get(route('sensors.create'))
        ->assertForbidden();
});

test('viewer cannot create a sensor', function () {
    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    $machine = Machine::factory()->create();

    $this->actingAs($viewer)
        ->post(route('sensors.store'), [
            'machine_id' => $machine->id,
            'code' => 'SNS-001',
            'name' => 'Temperature Sensor',
            'type' => 'temperature',
            'is_active' => true,
        ])
        ->assertForbidden();
});

test('admin can create a sensor', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $machine = Machine::factory()->create();

    $this->actingAs($admin)
        ->post(route('sensors.store'), [
            'machine_id' => $machine->id,
            'code' => 'SNS-001',
            'name' => 'Temperature Sensor',
            'type' => 'temperature',
            'is_active' => true,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('sensors', [
        'machine_id' => $machine->id,
        'code' => 'SNS-001',
        'name' => 'Temperature Sensor',
        'type' => 'temperature',
        'is_active' => true,
    ]);
});

test('admin can view sensor detail', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $sensor = Sensor::factory()->create();

    $this->actingAs($admin)
        ->get(route('sensors.show', $sensor))
        ->assertOk()
        ->assertViewIs('sensors.show');
});

test('viewer can view sensor detail', function () {
    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    $sensor = Sensor::factory()->create();

    $this->actingAs($viewer)
        ->get(route('sensors.show', $sensor))
        ->assertOk()
        ->assertViewIs('sensors.show');
});

test('viewer cannot edit a sensor', function () {
    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    $sensor = Sensor::factory()->create();

    $this->actingAs($viewer)
        ->get(route('sensors.edit', $sensor))
        ->assertForbidden();
});

test('admin can update a sensor', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $machine = Machine::factory()->create();
    $sensor = Sensor::factory()->create();

    $this->actingAs($admin)
        ->put(route('sensors.update', $sensor), [
            'machine_id' => $machine->id,
            'code' => 'SNS-UPDATED',
            'name' => 'Updated Sensor',
            'type' => 'production',
            'is_active' => false,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('sensors', [
        'id' => $sensor->id,
        'machine_id' => $machine->id,
        'code' => 'SNS-UPDATED',
        'name' => 'Updated Sensor',
        'type' => 'production',
        'is_active' => false,
    ]);
});

test('viewer cannot update a sensor', function () {
    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    $sensor = Sensor::factory()->create();

    $this->actingAs($viewer)
        ->put(route('sensors.update', $sensor), [
            'machine_id' => $sensor->machine_id,
            'code' => 'SNS-UPDATED',
            'name' => 'Updated Sensor',
            'type' => 'production',
            'is_active' => false,
        ])
        ->assertForbidden();
});

test('sensor code must be unique within the same machine', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $machine = Machine::factory()->create();

    Sensor::factory()->create([
        'machine_id' => $machine->id,
        'code' => 'SNS-001',
    ]);

    $this->actingAs($admin)
        ->post(route('sensors.store'), [
            'machine_id' => $machine->id,
            'code' => 'SNS-001',
            'name' => 'Another Sensor',
            'type' => 'temperature',
            'is_active' => true,
        ])
        ->assertSessionHasErrors('code');
});

test('sensor code can remain unchanged when updating', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $sensor = Sensor::factory()->create([
        'code' => 'SNS-001',
    ]);

    $this->actingAs($admin)
        ->put(route('sensors.update', $sensor), [
            'machine_id' => $sensor->machine_id,
            'code' => 'SNS-001',
            'name' => 'Updated Sensor',
            'type' => 'temperature',
            'is_active' => true,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});
