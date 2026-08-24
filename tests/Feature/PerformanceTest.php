<?php

use App\Models\Machine;
use App\Models\Sensor;
use App\Models\User;

test('machine index paginates results', function () {
    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    Machine::factory()->count(15)->create();

    $response = $this->actingAs($viewer)
        ->get(route('machines.index'));

    $response
        ->assertOk()
        ->assertViewIs('machines.index');

    $machines = $response->viewData('machines');

    expect($machines->count())->toBe(10)
        ->and($machines->perPage())->toBe(10)
        ->and($machines->total())->toBe(15)
        ->and($machines->currentPage())->toBe(1);
});

test('machine index returns the second page', function () {
    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    Machine::factory()->count(15)->create();

    $response = $this->actingAs($viewer)
        ->get(route('machines.index', ['page' => 2]));

    $response
        ->assertOk()
        ->assertViewIs('machines.index');

    $machines = $response->viewData('machines');

    expect($machines->count())->toBe(5)
        ->and($machines->currentPage())->toBe(2)
        ->and($machines->total())->toBe(15);
});

test('sensor index paginates results', function () {
    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    Sensor::factory()->count(15)->create();

    $response = $this->actingAs($viewer)
        ->get(route('sensors.index'));

    $response
        ->assertOk()
        ->assertViewIs('sensors.index');

    $sensors = $response->viewData('sensors');

    expect($sensors->count())->toBe(10)
        ->and($sensors->perPage())->toBe(10)
        ->and($sensors->total())->toBe(15)
        ->and($sensors->currentPage())->toBe(1);
});

test('sensor index returns the second page', function () {
    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    Sensor::factory()->count(15)->create();

    $response = $this->actingAs($viewer)
        ->get(route('sensors.index', ['page' => 2]));

    $response
        ->assertOk()
        ->assertViewIs('sensors.index');

    $sensors = $response->viewData('sensors');

    expect($sensors->count())->toBe(5)
        ->and($sensors->currentPage())->toBe(2)
        ->and($sensors->total())->toBe(15);
});
