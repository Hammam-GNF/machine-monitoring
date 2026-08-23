<?php

use App\Models\Machine;
use App\Models\User;

test('viewer can view machine index', function () {
    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    Machine::factory()->count(3)->create();

    $this->actingAs($viewer)
        ->get(route('machines.index'))
        ->assertOk()
        ->assertViewIs('machines.index');
});

test('admin can view machine index', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    Machine::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('machines.index'))
        ->assertOk()
        ->assertViewIs('machines.index');
});

test('guest cannot access machine management', function () {
    $this->get(route('machines.index'))
        ->assertRedirect(route('login'));
});

test('viewer cannot access machine create page', function () {
    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    $this->actingAs($viewer)
        ->get(route('machines.create'))
        ->assertForbidden();
});

test('viewer cannot create a machine', function () {
    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    $this->actingAs($viewer)
        ->post(route('machines.store'), [
            'code' => 'MC-001',
            'name' => 'Production Machine 1',
            'location' => 'Line A',
            'machine_type' => 'CNC',
            'installed_at' => '2026-08-23',
            'is_active' => true,
        ])
        ->assertForbidden();

    expect(Machine::query()->count())->toBe(0);
});

test('admin can create a machine', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post(route('machines.store'), [
            'code' => 'MC-001',
            'name' => 'Production Machine 1',
            'location' => 'Line A',
            'machine_type' => 'CNC',
            'installed_at' => '2026-08-23',
            'is_active' => true,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('machines', [
        'code' => 'MC-001',
        'name' => 'Production Machine 1',
        'location' => 'Line A',
        'machine_type' => 'CNC',
        'is_active' => true,
    ]);
});

test('admin can view machine detail', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $machine = Machine::factory()->create();

    $this->actingAs($admin)
        ->get(route('machines.show', $machine))
        ->assertOk()
        ->assertViewIs('machines.show');
});

test('viewer can view machine detail', function () {
    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    $machine = Machine::factory()->create();

    $this->actingAs($viewer)
        ->get(route('machines.show', $machine))
        ->assertOk()
        ->assertViewIs('machines.show');
});

test('viewer cannot edit a machine', function () {
    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    $machine = Machine::factory()->create();

    $this->actingAs($viewer)
        ->get(route('machines.edit', $machine))
        ->assertForbidden();
});

test('admin can update a machine', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $machine = Machine::factory()->create([
        'code' => 'MC-001',
    ]);

    $this->actingAs($admin)
        ->put(route('machines.update', $machine), [
            'code' => 'MC-002',
            'name' => 'Updated Machine',
            'location' => 'Line B',
            'machine_type' => 'CNC',
            'installed_at' => '2026-08-23',
            'is_active' => false,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('machines', [
        'id' => $machine->id,
        'code' => 'MC-002',
        'name' => 'Updated Machine',
        'location' => 'Line B',
        'machine_type' => 'CNC',
        'is_active' => false,
    ]);
});

test('viewer cannot update a machine', function () {
    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    $machine = Machine::factory()->create([
        'code' => 'MC-001',
    ]);

    $this->actingAs($viewer)
        ->put(route('machines.update', $machine), [
            'code' => 'MC-002',
            'name' => 'Updated Machine',
            'location' => 'Line B',
            'machine_type' => 'CNC',
            'installed_at' => '2026-08-23',
            'is_active' => false,
        ])
        ->assertForbidden();

    expect($machine->fresh()->code)->toBe('MC-001');
});

test('machine code must be unique when creating a machine', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    Machine::factory()->create([
        'code' => 'MC-001',
    ]);

    $this->actingAs($admin)
        ->post(route('machines.store'), [
            'code' => 'MC-001',
            'name' => 'Another Machine',
            'location' => 'Line B',
            'machine_type' => 'CNC',
            'installed_at' => '2026-08-23',
            'is_active' => true,
        ])
        ->assertSessionHasErrors('code');
});

test('machine code can remain unchanged when updating a machine', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $machine = Machine::factory()->create([
        'code' => 'MC-001',
    ]);

    $this->actingAs($admin)
        ->put(route('machines.update', $machine), [
            'code' => 'MC-001',
            'name' => 'Updated Machine',
            'location' => 'Line B',
            'machine_type' => 'CNC',
            'installed_at' => '2026-08-23',
            'is_active' => true,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});
