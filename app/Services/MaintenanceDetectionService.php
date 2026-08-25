<?php

namespace App\Services;

use App\Models\MaintenanceRecord;
use App\Models\SensorData;
use Illuminate\Support\Carbon;

class MaintenanceDetectionService
{
    private const HIGH_TEMPERATURE_THRESHOLD = 80.0;

    private const HIGH_TEMPERATURE_READINGS = 3;

    private const MACHINE_OFF_MINUTES = 30;

    public function detect(SensorData $sensorData): void
    {
        $this->detectHighTemperature($sensorData);

        $this->detectMachineOff($sensorData);
    }

    private function detectHighTemperature(SensorData $sensorData): void
    {
        if ($sensorData->temperature === null) {
            return;
        }

        $latestReadings = SensorData::query()
            ->where('machine_id', $sensorData->machine_id)
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->limit(self::HIGH_TEMPERATURE_READINGS)
            ->get();

        if ($latestReadings->count() < self::HIGH_TEMPERATURE_READINGS) {
            return;
        }

        $allReadingsAreHot = $latestReadings->every(
            fn (SensorData $reading) => $reading->temperature !== null
                && (float) $reading->temperature > self::HIGH_TEMPERATURE_THRESHOLD
        );

        if (! $allReadingsAreHot) {
            return;
        }

        $this->createMaintenanceIfNotOpen(
            $sensorData,
            'HIGH_TEMPERATURE'
        );
    }

    private function detectMachineOff(SensorData $sensorData): void
    {
        if ($sensorData->status !== 'OFF') {
            return;
        }

        $lastNonOffReading = SensorData::query()
            ->where('machine_id', $sensorData->machine_id)
            ->where('status', '!=', 'OFF')
            ->where('recorded_at', '<', $sensorData->recorded_at)
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->first();

        if (! $lastNonOffReading) {
            return;
        }

        $lastRecordedAt = $lastNonOffReading->recorded_at;
        $currentRecordedAt = $sensorData->recorded_at;

        /** @var Carbon $lastRecordedAt */
        /** @var Carbon $currentRecordedAt */
        $offDurationInSeconds = $lastRecordedAt->diffInSeconds($currentRecordedAt);

        if ($offDurationInSeconds <= self::MACHINE_OFF_MINUTES * 60) {
            return;
        }

        $this->createMaintenanceIfNotOpen(
            $sensorData,
            'MACHINE_OFF'
        );
    }

    private function createMaintenanceIfNotOpen(
        SensorData $sensorData,
        string $reason
    ): void {
        $hasOpenMaintenance = MaintenanceRecord::query()
            ->where('machine_id', $sensorData->machine_id)
            ->where('status', 'open')
            ->exists();

        if ($hasOpenMaintenance) {
            return;
        }

        MaintenanceRecord::create([
            'machine_id' => $sensorData->machine_id,
            'reason' => $reason,
            'detected_at' => $sensorData->recorded_at,
            'resolved_at' => null,
            'status' => 'open',
        ]);
    }
}
