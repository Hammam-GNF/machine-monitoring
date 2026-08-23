<?php

use App\Models\User;

test('admin can access admin routes', function () {
    $user = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Admin access granted.');
});

test('viewer cannot access admin routes', function () {
    $user = User::factory()->create([
        'role' => 'viewer',
    ]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

test('viewer can access viewer routes', function () {
    $user = User::factory()->create([
        'role' => 'viewer',
    ]);

    $this->actingAs($user)
        ->get('/viewer')
        ->assertOk()
        ->assertSee('Viewer access granted.');
});

test('admin cannot access viewer routes', function () {
    $user = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($user)
        ->get('/viewer')
        ->assertForbidden();
});

test('guest cannot access role protected routes', function () {
    $this->get('/admin')
        ->assertRedirect('/login');

    $this->get('/viewer')
        ->assertRedirect('/login');
});

test('user role helpers return correct values', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $viewer = User::factory()->create([
        'role' => 'viewer',
    ]);

    expect($admin->isAdmin())->toBeTrue()
        ->and($admin->isViewer())->toBeFalse()
        ->and($viewer->isAdmin())->toBeFalse()
        ->and($viewer->isViewer())->toBeTrue();
});
