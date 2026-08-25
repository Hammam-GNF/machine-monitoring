<?php

namespace App\Models;

use Database\Factories\SensorDataFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['event_id', 'machine_id', 'sensor_id', 'status', 'temperature', 'output', 'recorded_at', 'received_at', 'created_at'])]
class SensorData extends Model
{
    /** @use HasFactory<SensorDataFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'event_id' => 'string',
            'temperature' => 'decimal:2',
            'output' => 'integer',
            'recorded_at' => 'datetime',
            'received_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Machine, $this> */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /** @return BelongsTo<Sensor, $this> */
    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }
}
