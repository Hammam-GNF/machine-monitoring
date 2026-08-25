<?php

namespace App\Models;

use Database\Factories\MachineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['code', 'name', 'location', 'machine_type', 'installed_at', 'is_active'])]
class Machine extends Model
{
    /** @use HasFactory<MachineFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'installed_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<Sensor, $this> */
    public function sensors(): HasMany
    {
        return $this->hasMany(Sensor::class);
    }

    /** @return HasMany<SensorData, $this> */
    public function sensorData(): HasMany
    {
        return $this->hasMany(SensorData::class);
    }

    /** @return HasMany<MaintenanceRecord, $this> */
    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    /** @return HasOne<SensorData, $this> */
    public function latestSensorData(): HasOne
    {
        return $this->hasOne(SensorData::class)
            ->latestOfMany('recorded_at');
    }

    /** @return HasOne<MaintenanceRecord, $this> */
    public function openMaintenanceRecord(): HasOne
    {
        return $this->hasOne(MaintenanceRecord::class)
            ->where('status', 'open')
            ->latestOfMany('detected_at');
    }
}
