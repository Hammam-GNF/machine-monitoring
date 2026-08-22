<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'location', 'machine_type', 'installed_at', 'is_active'])]
class Machine extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'installed_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function sensors(): HasMany
    {
        return $this->hasMany(Sensor::class);
    }

    public function sensorData(): HasMany
    {
        return $this->hasMany(SensorData::class);
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }
}
