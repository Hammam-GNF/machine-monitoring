<?php

use App\Models\Machine;
use App\Models\MaintenanceRecord;

test('maintenance record belongs to a machine', function () {
    $machine = Machine::factory()->create();

    $maintenanceRecord = MaintenanceRecord::factory()
        ->for($machine)
        ->create();

    expect($maintenanceRecord->machine)
        ->toBeInstanceOf(Machine::class)
        ->and($maintenanceRecord->machine->is($machine))->toBeTrue();
});
