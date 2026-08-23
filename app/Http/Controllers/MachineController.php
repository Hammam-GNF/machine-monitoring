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

    public function destroy(Machine $machine): RedirectResponse
    {
        abort(404);
    }
}
