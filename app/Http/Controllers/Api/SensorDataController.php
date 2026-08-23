<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSensorDataRequest;
use App\Models\SensorData;
use Illuminate\Http\JsonResponse;

class SensorDataController extends Controller
{
    public function store(StoreSensorDataRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $existingSensorData = SensorData::where(
            'event_id',
            $validated['event_id']
        )->first();

        if ($existingSensorData) {
            return response()->json([
                'message' => 'Sensor data already received.',
                'data' => $existingSensorData,
            ], 200);
        }

        $sensorData = SensorData::create([
            ...$validated,
            'received_at' => now(),
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Sensor data received successfully.',
            'data' => $sensorData,
        ], 201);
    }
}
