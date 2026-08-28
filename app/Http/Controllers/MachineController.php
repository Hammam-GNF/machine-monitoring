<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMachineRequest;
use App\Http\Requests\UpdateMachineRequest;
use App\Models\Machine;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MachineController extends Controller
{
    public function index(): View
    {
        $machines = Machine::query()
            ->latest()
            ->paginate(10);

        return view('machines.index', compact('machines'));
    }

    public function create(): View
    {
        return view('machines.create');
    }

    public function store(StoreMachineRequest $request): RedirectResponse
    {
        $machine = Machine::create($request->validated());

        $machine->sensors()->create([
            'code' => 'SNS-'.str_pad(
                (string) $machine->id,
                4,
                '0',
                STR_PAD_LEFT
            ),
            'name' => 'Default Sensor',
            'type' => 'temperature',
        ]);

        return redirect()
            ->route('machines.show', $machine)
            ->with('success', 'Machine created successfully.');
    }

    public function show(Machine $machine): View
    {
        return view('machines.show', compact('machine'));
    }

    public function edit(Machine $machine): View
    {
        return view('machines.edit', compact('machine'));
    }

    public function update(
        UpdateMachineRequest $request,
        Machine $machine
    ): RedirectResponse {
        $machine->update($request->validated());

        return redirect()
            ->route('machines.show', $machine)
            ->with('success', 'Machine updated successfully.');
    }

    public function activate(Machine $machine): RedirectResponse
    {
        $machine->update([
            'is_active' => true,
        ]);

        return redirect()
            ->route('machines.index')
            ->with('success', 'Machine activated successfully.');
    }

    public function deactivate(Machine $machine): RedirectResponse
    {
        $machine->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('machines.index')
            ->with('success', 'Machine deactivated successfully.');
    }
}
