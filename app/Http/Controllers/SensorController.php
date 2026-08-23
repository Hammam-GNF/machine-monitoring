<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSensorRequest;
use App\Http\Requests\UpdateSensorRequest;
use App\Models\Machine;
use App\Models\Sensor;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SensorController extends Controller
{
    public function index(): View
    {
        $sensors = Sensor::query()
            ->with('machine')
            ->latest()
            ->paginate(10);

        return view('sensors.index', compact('sensors'));
    }

    public function create(): View
    {
        $machines = Machine::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('sensors.create', compact('machines'));
    }

    public function store(StoreSensorRequest $request): RedirectResponse
    {
        $sensor = Sensor::create($request->validated());

        return redirect()
            ->route('sensors.show', $sensor)
            ->with('success', 'Sensor created successfully.');
    }

    public function show(Sensor $sensor): View
    {
        $sensor->load('machine');

        return view('sensors.show', compact('sensor'));
    }

    public function edit(Sensor $sensor): View
    {
        $machines = Machine::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('sensors.edit', compact('sensor', 'machines'));
    }

    public function update(
        UpdateSensorRequest $request,
        Sensor $sensor
    ): RedirectResponse {
        $sensor->update($request->validated());

        return redirect()
            ->route('sensors.show', $sensor)
            ->with('success', 'Sensor updated successfully.');
    }

    public function destroy(Sensor $sensor): RedirectResponse
    {
        abort(404);
    }
}
