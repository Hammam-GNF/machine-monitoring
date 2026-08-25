# Machine Monitoring System - Database Design

## 1. Database

Database engine:

```text
Microsoft SQL Server
```

Primary application database:

```text
machine_monitoring
```

The Laravel application also contains framework tables such as cache, jobs, password reset tokens, and sessions. This document focuses on the five primary domain tables.

## 2. Primary Domain Tables

```text
users
machines
sensors
sensor_data
maintenance_records
```

## 3. Users

Authentication and authorization user.

| Column | Type | Null | Description |
| --- | --- | --- | --- |
| id | BIGINT | NO | Primary key |
| name | VARCHAR | NO | User name |
| email | VARCHAR | NO | Unique email |
| email_verified_at | TIMESTAMP | YES | Email verification timestamp |
| password | VARCHAR | NO | Password hash |
| role | VARCHAR(20) | NO | `admin` / `viewer` |
| remember_token | VARCHAR | YES | Laravel remember token |
| created_at | TIMESTAMP | YES | Laravel timestamp |
| updated_at | TIMESTAMP | YES | Laravel timestamp |

Constraints:

```text
PRIMARY KEY (id)
UNIQUE (email)
```

Default role:

```text
viewer
```

## 4. Machines

Master data mesin.

| Column | Type | Null | Description |
| --- | --- | --- | --- |
| id | BIGINT | NO | Primary key |
| code | VARCHAR(50) | NO | Unique machine code |
| name | VARCHAR(100) | NO | Machine name |
| location | VARCHAR(100) | NO | Location / line |
| machine_type | VARCHAR(100) | NO | Machine type |
| installed_at | DATE | NO | Installation date |
| is_active | BIT | NO | Active / inactive |
| created_at | TIMESTAMP | YES | Laravel timestamp |
| updated_at | TIMESTAMP | YES | Laravel timestamp |

Constraints:

```text
PRIMARY KEY (id)
UNIQUE (code)
```

Indexes:

```text
INDEX(location)
INDEX(machine_type)
INDEX(is_active)
```

## 5. Sensors

Sensor yang dimiliki oleh machine.

| Column | Type | Null | Description |
| --- | --- | --- | --- |
| id | BIGINT | NO | Primary key |
| machine_id | BIGINT | NO | Machine foreign key |
| code | VARCHAR(50) | NO | Sensor code |
| name | VARCHAR(100) | NO | Sensor name |
| type | VARCHAR(30) | NO | Sensor type |
| is_active | BIT | NO | Active / inactive |
| created_at | TIMESTAMP | YES | Laravel timestamp |
| updated_at | TIMESTAMP | YES | Laravel timestamp |

Constraints:

```text
PRIMARY KEY (id)

FOREIGN KEY (machine_id)
REFERENCES machines(id)

UNIQUE (machine_id, code)
```

The machine foreign key uses cascade delete.

Index:

```text
INDEX(machine_id)
```

## 6. Sensor Data

Historical sensor readings.

This is expected to be the largest table in the application.

| Column | Type | Null | Description |
| --- | --- | --- | --- |
| id | BIGINT | NO | Primary key |
| event_id | UNIQUEIDENTIFIER | NO | Idempotency key |
| machine_id | BIGINT | NO | Machine foreign key |
| sensor_id | BIGINT | NO | Sensor foreign key |
| status | VARCHAR(3) | NO | `ON` / `OFF` |
| temperature | DECIMAL(6,2) | YES | Temperature in °C |
| output | INT | NO | Production output for the period |
| recorded_at | DATETIME | NO | Sensor reading time |
| received_at | DATETIME | NO | Server receive time |
| created_at | TIMESTAMP | YES | Laravel timestamp |

Constraints:

```text
PRIMARY KEY (id)

UNIQUE (event_id)

FOREIGN KEY (machine_id)
REFERENCES machines(id)

FOREIGN KEY (sensor_id)
REFERENCES sensors(id)
```

The machine and sensor foreign keys use no-action-on-delete behavior.

Indexes:

```text
UNIQUE(event_id)

INDEX(machine_id, recorded_at)

INDEX(sensor_id, recorded_at)

INDEX(recorded_at)
```

A covering index is also added for the relevant `recorded_at` query pattern as part of the scalability optimization work.

## 7. Maintenance Records

Machine maintenance events detected by the system.

| Column | Type | Null | Description |
| --- | --- | --- | --- |
| id | BIGINT | NO | Primary key |
| machine_id | BIGINT | NO | Machine foreign key |
| reason | VARCHAR(50) | NO | Maintenance reason |
| detected_at | DATETIME | NO | Detection time |
| resolved_at | DATETIME | YES | Resolution time |
| status | VARCHAR(20) | NO | `open` / `resolved` |
| created_at | TIMESTAMP | YES | Laravel timestamp |
| updated_at | TIMESTAMP | YES | Laravel timestamp |

Constraints:

```text
PRIMARY KEY (id)

FOREIGN KEY (machine_id)
REFERENCES machines(id)
```

The machine foreign key uses no-action-on-delete behavior.

Indexes:

```text
INDEX(machine_id, status)

INDEX(detected_at)
```

## 8. Relationships

```text
users
  │
  └── Authentication only


machines
   │
   ├── 1:N ── sensors
   │             │
   │             └── 1:N ── sensor_data
   │
   └── 1:N ── maintenance_records
```

Eloquent relationships:

### Machine

```text
hasMany(Sensor)
hasMany(SensorData)
hasMany(MaintenanceRecord)
```

### Sensor

```text
belongsTo(Machine)
hasMany(SensorData)
```

### SensorData

```text
belongsTo(Machine)
belongsTo(Sensor)
```

### MaintenanceRecord

```text
belongsTo(Machine)
```

## 9. Shift Calculation

Shift is not stored in the database.

It is calculated from:

```text
sensor_data.recorded_at
```

| Shift | Time |
| --- | --- |
| Shift 1 | 06:00–14:00 |
| Shift 2 | 14:00–22:00 |
| Shift 3 | 22:00–06:00 |

## 10. Production Calculation

Production uses:

```text
sensor_data.output
```

`output` represents the number of units produced during the sensor reading period. It is not a cumulative production meter.

Example:

```text
18:00 → 120
18:05 → 115
18:10 → 125
```

Total:

```text
360 units
```

## 11. Expected Data Volume

| Table | Expected Volume |
| --- | ---: |
| users | Small |
| machines | Tens |
| sensors | Tens / hundreds |
| sensor_data | 100k+ |
| maintenance_records | Small / medium |

The `sensor_data` table is the primary scalability concern.

## 12. ERD

The visual ERD is provided separately as:

```text
docs/database-erd.mmd
```

Relationship summary:

```text
users
  │
  └── authentication only

machines
   │
   ├───────────────┐
   │               │
   │ 1:N           │ 1:N
   ▼               ▼
sensors      maintenance_records
   │
   │ 1:N
   ▼
sensor_data
```
