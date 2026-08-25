# Machine Monitoring System - Architecture

## 1. Architecture Overview

Machine Monitoring System menggunakan Laravel sebagai application framework dan Microsoft SQL Server sebagai relational database.

Architecture dibuat sederhana dan mengikuti kebutuhan MVP. Business logic dipisahkan dari HTTP layer melalui service dan action classes ketika memang memberikan nilai.

```text
┌──────────────────────┐
│   Web Browser / UI   │
│ Dashboard            │
│ Machine Management   │
│ Sensor Management    │
│ Production Report    │
└──────────┬───────────┘
           │ HTTP
           ▼
┌──────────────────────┐
│       Laravel        │
│                      │
│ Authentication       │
│ Controllers          │
│ Form Requests        │
│ Actions / Services   │
│ Eloquent Models      │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│     SQL Server       │
│                      │
│ users                │
│ machines             │
│ sensors              │
│ sensor_data          │
│ maintenance_records  │
└──────────────────────┘
```

IoT data follows a separate API flow:

```text
IoT Simulator
     │
     │ HTTP POST
     ▼
Laravel API
     │
     ▼
Controller
     │
     ▼
Form Request
     │
     ▼
Action / Service
     │
     ├── Validate event
     ├── Validate machine
     ├── Validate sensor
     ├── Check idempotency
     └── Store sensor reading
            │
            ▼
       SQL Server
```

## 2. Application Layers

### HTTP Layer

The HTTP layer handles requests from the browser and IoT simulator.

Main responsibilities:

- Controllers
- Form Requests
- Middleware
- Authentication and authorization

Controllers should coordinate the request flow and avoid containing unnecessary business logic.

### Application / Service Layer

Actions and services contain business logic that should not be placed directly in controllers.

Examples include:

- Sensor data processing
- Maintenance detection
- Production aggregation
- Machine monitoring queries

Abstractions are introduced only when they provide meaningful separation or reuse.

### Model Layer

Eloquent Models represent database entities and handle:

- Database representation
- Relationships
- Attribute handling
- Query interaction

## 3. Directory Structure

The application follows a conventional Laravel structure with additional separation for actions and services.

```text
app/
├── Actions/
├── Concerns/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Livewire/
├── Models/
├── Policies/
├── Providers/
└── Services/

database/
├── factories/
├── migrations/
└── seeders/

resources/
├── css/
├── js/
└── views/

routes/
├── api.php
└── web.php

tests/
├── Feature/
└── Unit/
```

The project intentionally avoids repository, DTO, or other abstraction layers when they do not provide meaningful value for the current scope.

## 4. Authentication & Authorization

Authentication uses Laravel Fortify.

User roles are stored directly in:

```text
users.role
```

MVP roles:

- `admin`
- `viewer`

Authorization uses Laravel's authorization mechanisms and policies to protect management actions.

## 5. Machine Monitoring Flow

A machine is the root entity for monitoring.

```text
Machine
   │
   ├── 1:N ── Sensor
   │            │
   │            └── 1:N ── Sensor Data
   │
   └── 1:N ── Maintenance Records
```

A machine can have multiple sensors. Each sensor can produce multiple historical sensor readings.

## 6. IoT Data Flow

The IoT simulator sends sensor readings through the sensor data API.

```text
IoT Simulator
      │
      │ POST /api/sensor-data
      ▼
SensorDataController
      │
      ▼
StoreSensorDataRequest
      │
      ▼
Sensor Data Processing
      │
      ├── Validate event
      ├── Validate machine
      ├── Validate sensor
      ├── Check idempotency
      └── Store reading
             │
             ▼
         SQL Server
```

Sensor data processing is also the entry point for evaluating machine conditions that can trigger maintenance detection.

## 7. Idempotency

Every sensor event contains an:

```text
event_id
```

`event_id` must be unique.

If the same event is submitted more than once, the application must not create duplicate sensor data.

Idempotency is protected at both the application and database levels. The `sensor_data.event_id` column has a unique constraint, providing database-level protection against duplicate events.

## 8. Maintenance Detection Flow

Maintenance detection evaluates incoming sensor data against the defined machine conditions.

```text
Sensor Data
     │
     ▼
Evaluate Machine Condition
     │
     ├── 3 consecutive temperature readings > 80°C
     │             │
     │             ▼
     │      HIGH_TEMPERATURE
     │
     └── Machine OFF > 30 minutes
                   │
                   ▼
            Maintenance Event
                   │
                   ▼
         Maintenance Record
```

Maintenance records support:

- `open`
- `resolved`

## 9. Reporting Flow

Production reports are calculated from historical sensor data.

```text
sensor_data
     │
     ▼
Query + Aggregation
     │
     ├── Date
     ├── Shift
     ├── Day
     └── Month
     │
     ▼
Production Report
```

Shift is not stored as a database entity. It is calculated from `sensor_data.recorded_at`.

Shift definitions:

- Shift 1: `06:00–14:00`
- Shift 2: `14:00–22:00`
- Shift 3: `22:00–06:00`

## 10. Performance Considerations

The primary scalability concern is:

```text
sensor_data
```

The application is designed to support large sensor datasets, including 100,000+ records.

Important query indexes include:

```text
(machine_id, recorded_at)
(sensor_id, recorded_at)
(recorded_at)
```

Pagination and query optimization are used for large result sets.

A covering index is also included for relevant `recorded_at` access patterns.

Performance validation covers:

- Large dataset generation
- Query execution
- Index effectiveness
- Pagination behavior

## 11. Testing Strategy

Testing uses Pest.

### Feature Tests

Coverage includes:

- Authentication
- Machine management
- Sensor management
- Sensor data API
- Idempotency
- Authorization
- Maintenance detection
- Reporting

### Database Tests

Coverage includes:

- Relationships
- Constraints
- Unique fields
- Foreign keys

### Performance Tests

Performance validation is performed separately for:

- Large datasets
- Query execution
- Index effectiveness
- Pagination
