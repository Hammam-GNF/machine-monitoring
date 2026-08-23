<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SensorDataController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $sensorData = SensorData::create([
            'event_id' => $request->input('event_id'),
            'machine_id' => $request->input('machine_id'),
            'sensor_id' => $request->input('sensor_id'),
            'status' => $request->input('status'),
            'temperature' => $request->input('temperature'),
            'output' => $request->input('output'),
            'recorded_at' => $request->input('recorded_at'),
            'received_at' => now(),
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Sensor data received successfully.',
            'data' => $sensorData,
        ], 201);
    }
}
