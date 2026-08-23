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
        $sensorData = SensorData::create([
            ...$request->validated(),
            'received_at' => now(),
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Sensor data received successfully.',
            'data' => $sensorData,
        ], 201);
    }
}
