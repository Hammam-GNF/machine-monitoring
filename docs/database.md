# Machine Monitoring System - Database Design

## 1. Database

Database engine:

```text
Microsoft SQL Server
```

Primary database:

machine_monitoring

## 2. Tables

The MVP contains five primary tables:

users
machines
sensors
sensor_data
maintenance_records

## 3. Users

Authentication user.
| Column     | Type          | Null | Description        |
| ---------- | ------------- | ---- | ------------------ |
| id         | BIGINT        | NO   | Primary key        |
| name       | NVARCHAR(255) | NO   | User name          |
| email      | NVARCHAR(255) | NO   | Unique email       |
| password   | NVARCHAR(255) | NO   | Password hash      |
| role       | VARCHAR(20)   | NO   | `admin` / `viewer` |
| created_at | DATETIME2     | YES  | Laravel timestamp  |
| updated_at | DATETIME2     | YES  | Laravel timestamp  |

Constraints:

PRIMARY KEY (id)
UNIQUE (email)

## 4. Machines

Master data mesin.

| Column       | Type          | Null | Description         |
| ------------ | ------------- | ---- | ------------------- |
| id           | BIGINT        | NO   | Primary key         |
| code         | VARCHAR(50)   | NO   | Unique machine code |
| name         | NVARCHAR(100) | NO   | Machine name        |
| location     | NVARCHAR(100) | NO   | Location / line     |
| machine_type | NVARCHAR(100) | NO   | Machine type        |
| installed_at | DATE          | NO   | Installation date   |
| is_active    | BIT           | NO   | Active / inactive   |
| created_at   | DATETIME2     | YES  | Laravel timestamp   |
| updated_at   | DATETIME2     | YES  | Laravel timestamp   |

Constraints:

PRIMARY KEY (id)
UNIQUE (code)

Indexes:

INDEX(location)
INDEX(machine_type)
INDEX(is_active)

## 5. Sensors

Sensor yang dimiliki oleh machine.

| Column     | Type          | Null | Description         |
| ---------- | ------------- | ---- | ------------------- |
| id         | BIGINT        | NO   | Primary key         |
| machine_id | BIGINT        | NO   | Machine foreign key |
| code       | VARCHAR(50)   | NO   | Sensor code         |
| name       | NVARCHAR(100) | NO   | Sensor name         |
| type       | VARCHAR(30)   | NO   | Sensor type         |
| is_active  | BIT           | NO   | Active / inactive   |
| created_at | DATETIME2     | YES  | Laravel timestamp   |
| updated_at | DATETIME2     | YES  | Laravel timestamp   |

Constraints:

PRIMARY KEY (id)


FOREIGN KEY (machine_id)
REFERENCES machines(id)


UNIQUE (machine_id, code)

Indexes:

INDEX(machine_id)

## 6. Sensor Data

Historical sensor readings.

This table is expected to contain the largest amount of data.

| Column      | Type             | Null | Description                      |
| ----------- | ---------------- | ---- | -------------------------------- |
| id          | BIGINT           | NO   | Primary key                      |
| event_id    | UNIQUEIDENTIFIER | NO   | Idempotency key                  |
| machine_id  | BIGINT           | NO   | Machine foreign key              |
| sensor_id   | BIGINT           | NO   | Sensor foreign key               |
| status      | VARCHAR(3)       | NO   | `ON` / `OFF`                     |
| temperature | DECIMAL(6,2)     | YES  | Temperature in °C                |
| output      | INT              | NO   | Production output for the period |
| recorded_at | DATETIME2        | NO   | Sensor reading time              |
| received_at | DATETIME2        | NO   | Server receive time              |
| created_at  | DATETIME2        | YES  | Laravel timestamp                |

Constraints:

PRIMARY KEY (id)


UNIQUE (event_id)


FOREIGN KEY (machine_id)
REFERENCES machines(id)


FOREIGN KEY (sensor_id)
REFERENCES sensors(id)

Indexes:

UNIQUE(event_id)


INDEX(machine_id, recorded_at)


INDEX(sensor_id, recorded_at)


INDEX(recorded_at)

## 7. Maintenance Records

Machine maintenance events detected by the system.

| Column      | Type        | Null | Description         |
| ----------- | ----------- | ---- | ------------------- |
| id          | BIGINT      | NO   | Primary key         |
| machine_id  | BIGINT      | NO   | Machine foreign key |
| reason      | VARCHAR(50) | NO   | Maintenance reason  |
| detected_at | DATETIME2   | NO   | Detection time      |
| resolved_at | DATETIME2   | YES  | Resolution time     |
| status      | VARCHAR(20) | NO   | `open` / `resolved` |
| created_at  | DATETIME2   | YES  | Laravel timestamp   |
| updated_at  | DATETIME2   | YES  | Laravel timestamp   |

Constraints:

PRIMARY KEY (id)


FOREIGN KEY (machine_id)
REFERENCES machines(id)

Indexes:

INDEX(machine_id, status)


INDEX(detected_at)

## 8. Relationships

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

Machine
hasMany Sensors
hasMany SensorData
hasMany MaintenanceRecords
Sensor
belongsTo Machine
hasMany SensorData
SensorData
belongsTo Machine
belongsTo Sensor
MaintenanceRecord
belongsTo Machine

## 9. Shift Calculation

Shift tidak disimpan dalam database.

Shift dihitung berdasarkan sensor_data.recorded_at.

Shift 1
06:00 – 14:00


Shift 2
14:00 – 22:00


Shift 3
22:00 – 06:00

## 10. Production Calculation

Production menggunakan:

sensor_data.output

output merupakan jumlah unit produksi pada periode sensor tersebut.

Contoh:

18:00 → 120
18:05 → 115
18:10 → 125

Total:

360 units

Nilai tersebut bukan cumulative meter.

## 11. Expected Data Volume

| Table               | Expected Volume |
| ------------------- | --------------: |
| users               |           Small |
| machines            |            Tens |
| sensors             | Tens / hundreds |
| sensor_data         |           100k+ |
| maintenance_records |  Small / medium |

## 12. ERD

┌──────────────────────────┐
│          USERS           │
├──────────────────────────┤
│ PK id                    │
│    name                  │
│    email UNIQUE          │
│    password              │
│    role                  │
│    created_at            │
│    updated_at            │
└──────────────────────────┘


┌──────────────────────────┐
│        MACHINES          │
├──────────────────────────┤
│ PK id                    │
│    code UNIQUE           │
│    name                  │
│    location               │
│    machine_type          │
│    installed_at          │
│    is_active              │
│    created_at            │
│    updated_at            │
└───────────┬──────────────┘
            │
      ┌─────┴───────────────┐
      │                     │
      │ 1:N                 │ 1:N
      ▼                     ▼
┌──────────────────┐   ┌─────────────────────────┐
│     SENSORS      │   │  MAINTENANCE_RECORDS    │
├──────────────────┤   ├─────────────────────────┤
│ PK id            │   │ PK id                   │
│ FK machine_id    │   │ FK machine_id           │
│    code          │   │    reason               │
│    name          │   │    detected_at          │
│    type          │   │    resolved_at           │
│    is_active     │   │    status                │
│    created_at    │   │    created_at            │
│    updated_at    │   │    updated_at            │
└────────┬─────────┘   │    updated_at            │
         │             └─────────────────────────┘
         │ 1:N
         ▼
┌──────────────────────────────┐
│         SENSOR_DATA          │
├──────────────────────────────┤
│ PK id                        │
│    event_id UNIQUE           │
│ FK machine_id                │
│ FK sensor_id                 │
│    status                    │
│    temperature               │
│    output                    │
│    recorded_at               │
│    received_at               │
│    created_at                │
└──────────────────────────────┘


---