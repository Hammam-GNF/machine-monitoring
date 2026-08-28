# Machine Monitoring System

Machine Monitoring System adalah aplikasi monitoring mesin berbasis IoT yang dibangun untuk memenuhi **Technical Challenge — Web Developer: Sistem Monitoring Mesin Berbasis IoT**.

Aplikasi menyediakan:

- Master data mesin dan sensor virtual
- Authentication dan role Admin / Viewer
- HTTP API untuk menerima data sensor dari pihak eksternal
- External IoT device simulator
- Monitoring status mesin secara near-real-time melalui polling
- Deteksi kebutuhan maintenance berbasis data sensor
- Rekap output produksi berdasarkan hari, bulan, dan shift
- Generator 100.000+ historical sensor data
- Query benchmark dan SQL Server execution plan
- Automated testing dan static analysis

> **Database utama:** Microsoft SQL Server
> **Framework:** Laravel 13
> **UI:** Livewire 4 + Flux

---

## 1. Technical Challenge Coverage

| Requirement | Implementation | Status |
|---|---|---|
| Microsoft SQL Server | Laravel `sqlsrv` connection | Implemented |
| Machine master data | Machine management | Implemented |
| Machine code, name, location, type, installation date | `machines` table + management UI | Implemented |
| Active / inactive machine status | Machine lifecycle actions | Implemented |
| Minimum 1 virtual sensor per created machine | Default sensor created when machine is created | Implemented |
| Sensor management | Sensor CRUD-style management | Implemented |
| Sensor API | `POST /api/sensor-data` | Implemented |
| API validation | Form Request + machine/sensor relationship validation | Implemented |
| IoT duplicate handling | `event_id` idempotency + unique database constraint | Implemented |
| External device simulator | `device-simulator/` | Implemented |
| Periodic sensor transmission | Default 5-second simulator interval | Implemented |
| Dashboard monitoring | Machine status, temperature, maintenance state | Implemented |
| Near-real-time update | Dashboard polling endpoint | Implemented |
| Dashboard filters/search | Location, type, status, maintenance, search | Implemented |
| Maintenance detection | High temperature + prolonged OFF rules | Implemented |
| Production report | Date range, shift, daily/monthly aggregation | Implemented |
| Output aggregation | Total output + average output/hour | Implemented |
| Uptime / downtime | Reading-status based calculation | Implemented |
| Admin / Viewer roles | Role middleware + protected management actions | Implemented |
| 100.000+ historical records | `app:generate-sensor-data` | Implemented |
| Performance benchmark | `app:benchmark-sensor-data` | Implemented |
| Indexing | Composite and supporting indexes | Implemented |
| SQL Server execution plan | `docs/Execution plan.sqlplan` | Included |
| README / technical documentation | `README.md` + `docs/` | Included |

The challenge's functional scope is covered. The main documented caveat is that the benchmark shows one intentionally broad daily aggregation query can exceed the challenge's suggested 3–5 second target on the local development environment; this is documented rather than hidden.

---

## 2. Application Overview

The system models a production environment where machines are equipped with virtual IoT sensors.

```text
                     ┌─────────────────────┐
                     │   Web Browser / UI  │
                     │                     │
                     │ Dashboard           │
                     │ Machine Management  │
                     │ Sensor Management   │
                     │ Production Report   │
                     └──────────┬──────────┘
                                │ HTTP
                                ▼
                     ┌─────────────────────┐
                     │   Laravel 13 App   │
                     │                     │
                     │ Controllers         │
                     │ Form Requests       │
                     │ Services            │
                     │ Eloquent Models     │
                     │ Authentication      │
                     └──────────┬──────────┘
                                │
                                ▼
                     ┌─────────────────────┐
                     │   Microsoft SQL    │
                     │       Server        │
                     └─────────────────────┘
                                ▲
                                │
                     ┌──────────┴──────────┐
                     │  External IoT       │
                     │  Device Simulator   │
                     └─────────────────────┘
```

Detailed architecture documentation:

- [Architecture](docs/architecture.md)
- [Architecture Diagram](docs/architecture-diagram.mmd)

---

## 3. Tech Stack

| Technology | Purpose |
|---|---|
| Laravel 13 | Backend and application framework |
| PHP 8.3+ | Runtime |
| Microsoft SQL Server | Primary relational database |
| Livewire 4 | Reactive UI |
| Flux | UI component library |
| Laravel Fortify | Authentication |
| Pest 4 | Automated testing |
| Larastan | Static analysis |
| Laravel Pint | Code formatting |
| Vite | Frontend asset bundling |

### Why Laravel?

Laravel was selected because it provides a productive full-stack PHP framework with:

- Strong support for SQL Server through Laravel's database layer
- Form Request validation
- Authentication and authorization support
- Eloquent relationships
- Database migrations and factories
- Testing support through Pest
- Clear separation between HTTP, application/service, and model responsibilities

The project intentionally avoids unnecessary repository/DTO abstractions where they would add complexity without meaningful value for this challenge.

---

## 4. Functional Features

### 4.1 Authentication & Authorization

The application supports two roles:

#### Admin

Admin can:

- Manage machines
- Create and update sensors
- Activate / deactivate machines
- Perform protected management operations

#### Viewer

Viewer can:

- View the dashboard
- View machines
- View sensors
- View production reports

Viewer cannot perform protected management operations.

---

### 4.2 Machine Management

Machine master data contains:

- Machine code
- Machine name
- Location / production line
- Machine type
- Installation date
- Active / inactive status

Machine list is paginated and management actions are protected by the Admin role.

#### Machine lifecycle policy

The technical challenge asks for CRUD machine management. For deletion, this project deliberately does **not** perform destructive hard deletion.

A machine can have:

```text
Machine
 ├── Sensor
 ├── Sensor Data
 └── Maintenance Records
```

Sensor data is historical IoT data and should remain available for reporting and audit purposes.

Therefore, the machine lifecycle is:

```text
ACTIVE
  │
  ▼
INACTIVE
```

instead of:

```text
ACTIVE
  │
  ▼
DELETE
```

This preserves historical sensor and maintenance records while still providing the operational equivalent of removing a machine from the active master-data population.

The implementation exposes explicit Admin actions:

```text
POST /machines/{machine}/activate
POST /machines/{machine}/deactivate
```

A destructive machine `DELETE` endpoint is intentionally not exposed.

---

### 4.3 Virtual Sensors

Every machine created through the Machine Management flow automatically receives one default virtual sensor.

Example:

```text
Machine
  └── Default Sensor
       ├── Code
       ├── Name
       ├── Type
       └── Active status
```

Additional sensors can also be managed through Sensor Management.

The seeded demo environment contains 10 machines with 3 sensors per machine.

---

## 5. IoT Sensor Data API

The application receives data from an external device through:

```http
POST /api/sensor-data
```

Example payload:

```json
{
    "event_id": "550e8400-e29b-41d4-a716-446655440000",
    "machine_id": 1,
    "sensor_id": 1,
    "status": "ON",
    "temperature": 72.50,
    "output": 120,
    "recorded_at": "2026-08-25 10:00:00"
}
```

The API validates:

- `event_id` must be a UUID
- Machine must exist
- Sensor must exist
- Sensor must belong to the selected machine
- Status must be `ON` or `OFF`
- Temperature must be numeric when supplied
- Output must be a non-negative integer
- `recorded_at` must contain a valid date/time

The API returns a clear validation response when the request is invalid.

### Idempotency

The selected IoT failure scenario is **duplicate sensor data**.

Each event contains a unique:

```text
event_id
```

The application checks for an existing event before creating a new sensor-data record.

The database also protects the same invariant through a unique constraint on `sensor_data.event_id`.

Therefore:

```text
First request
    ↓
New sensor data created

Repeated request with same event_id
    ↓
Existing sensor data returned
    ↓
No duplicate record created
```

This provides protection at both the application and database levels.

---

## 6. External IoT Device Simulator

The simulator is intentionally separated from the Laravel application:

```text
device-simulator/
├── devices.json
└── simulator.php
```

This represents an external IoT device or IoT team rather than Laravel generating sensor data internally.

The simulator:

- Reads machine/sensor mappings from `devices.json`
- Generates a UUID event ID for each transmission
- Generates `ON` / `OFF` status
- Generates temperature readings
- Generates production output
- Sends HTTP POST requests to `/api/sensor-data`
- Sends data periodically
- Reports HTTP/API failures to the console

Default interval:

```text
5 seconds
```

### Run the simulator

Start the Laravel application first:

```bash
composer run dev
```

Then, in another terminal:

```bash
php device-simulator/simulator.php
```

Optional environment variables:

```text
SENSOR_API_URL
SIMULATOR_INTERVAL
```

For example:

```powershell
$env:SENSOR_API_URL="http://127.0.0.1:8000/api/sensor-data"
$env:SIMULATOR_INTERVAL="5"
php device-simulator/simulator.php
```

The default `devices.json` is aligned with the seeded demo dataset:

```text
10 machines
3 sensors per machine
30 simulator device mappings
```

---

## 7. Dashboard Monitoring

The dashboard provides:

- Total machines
- Active machines
- Machines requiring maintenance
- Current machine status
- Latest temperature
- Maintenance status
- Search
- Location filter
- Machine type filter
- Status filter
- Maintenance filter

The dashboard receives machine data through a dedicated polling endpoint:

```http
GET /dashboard/data
```

The UI periodically requests updated data without requiring a full page refresh.

This provides near-real-time monitoring suitable for the technical challenge without introducing unnecessary WebSocket infrastructure.

Detailed implementation:

- `DashboardController`
- `DashboardPollingController`
- `DashboardService`

---

## 8. Maintenance Detection

Maintenance detection is based on sensor data rather than a manually entered maintenance flag.

Two rules are implemented.

### Rule 1 — High Temperature

A machine requires maintenance when the latest three machine sensor readings are all above:

```text
80°C
```

Condition:

```text
temperature > 80°C
```

for:

```text
3 consecutive readings
```

The resulting maintenance reason is:

```text
HIGH_TEMPERATURE
```

### Rule 2 — Prolonged OFF State

A machine requires maintenance when an OFF reading indicates that the machine has remained OFF for more than:

```text
30 minutes
```

The duration is calculated from the latest preceding non-OFF reading to the current OFF reading.

The resulting maintenance reason is:

```text
MACHINE_OFF
```

### Open maintenance protection

The system does not continuously create duplicate open maintenance records for the same machine.

Before creating a maintenance event, the application checks whether the machine already has an open maintenance record.

Maintenance records support:

```text
open
resolved
```

Detailed requirements and maintenance assumptions are documented in:

- [Requirements](docs/requirements.md)
- [Architecture](docs/architecture.md)

---

## 9. Production Reporting

Production reporting uses:

```text
sensor_data.output
```

`output` represents the number of units produced during the sensor reading period. It is **not** a cumulative production meter.

The report supports:

- Machine filter
- Date range
- Shift filter
- Daily aggregation
- Monthly aggregation
- Total output
- Average output per hour
- Uptime percentage
- Downtime percentage
- Pagination

### Shift definitions

| Shift | Time |
|---|---|
| Shift 1 | 06:00–14:00 |
| Shift 2 | 14:00–22:00 |
| Shift 3 | 22:00–06:00 |

Shift is calculated from `recorded_at` and is not stored as a separate database entity.

Detailed reporting rules:

- [Production Reporting](docs/reporting.md)

---

## 10. Database Design

Primary domain tables:

```text
users
machines
sensors
sensor_data
maintenance_records
```

Relationships:

```text
machines
   │
   ├── 1:N ── sensors
   │             │
   │             └── 1:N ── sensor_data
   │
   ├── 1:N ── sensor_data
   │
   └── 1:N ── maintenance_records
```

The `sensor_data` table is expected to be the largest table.

### Important indexes

The application uses indexes for common join/filter/order patterns, including:

```text
machines.location
machines.machine_type
machines.is_active

sensors.machine_id

sensor_data.event_id
sensor_data.machine_id + recorded_at
sensor_data.sensor_id + recorded_at
sensor_data.recorded_at
sensor_data.recorded_at + output

maintenance_records.machine_id + status
maintenance_records.detected_at
```

The composite indexes are particularly important for historical sensor queries.

Detailed schema:

- [Database Design](docs/database.md)
- [Database ERD](docs/database-erd.mmd)

---

## 11. Scalability & Large Dataset Testing

The technical challenge requires at least 100,000 historical sensor records.

The project provides a dedicated command:

```bash
php artisan app:generate-sensor-data
```

Default:

```text
100,000 records
200 records per insert batch
30-day historical period
```

The command uses query-builder bulk inserts rather than creating each record through an individual Eloquent `create()` call.

Options:

```bash
php artisan app:generate-sensor-data \
    --count=100000 \
    --chunk=200 \
    --days=30
```

Windows PowerShell:

```powershell
php artisan app:generate-sensor-data --count=100000 --chunk=200 --days=30
```

The command reports:

- Records generated
- Batch size
- Historical period
- Total duration
- Throughput in rows/second

---

## 12. Performance Benchmark

A dedicated benchmark command is available:

```bash
php artisan app:benchmark-sensor-data
```

The benchmark covers representative queries:

- Latest sensor data per machine
- Production aggregation by day
- Production aggregation by month
- Production aggregation by date range
- Production aggregation by date range and shift

The documented benchmark was executed with:

```text
101,000 sensor data records
10 machines
30-day historical range
```

### Observed benchmark

| Query | Average |
|---|---:|
| Latest sensor data per machine | 4,035.98 ms |
| Production aggregation by day | 22,259.28 ms |
| Production aggregation by month | 54.81 ms |
| Production aggregation by date range | 22.45 ms |
| Production aggregation by date range + shift | 166.83 ms |

These values are environment-specific measurements from a local development machine.

### Important interpretation

The benchmark does **not** claim that every query meets the challenge's suggested `< 3–5 seconds` target.

Most filtered/date-range/month/shift queries are within or comfortably below that range, while the broad daily aggregation across the complete historical dataset is substantially slower.

That daily query intentionally processes the entire historical dataset:

```sql
SELECT
    CAST(recorded_at AS date) AS date,
    SUM(output) AS total_output
FROM sensor_data
GROUP BY CAST(recorded_at AS date)
ORDER BY date;
```

The current optimization decision was to keep the existing indexes rather than add an index solely to disguise or overfit this full-history aggregation. The execution plan shows that SQL Server is already using an appropriate sensor-data index for the tested workload.

For a larger production system, further optimization could include pre-aggregated daily summary tables or another reporting-oriented strategy. Those are intentionally outside the current challenge scope.

Full benchmark evidence:

- [Performance Benchmark](docs/performance.md)
- [SQL Server Execution Plan](docs/Execution%20plan.sqlplan)

---

## 13. Execution Plan

The repository includes an actual SQL Server execution-plan file:

```text
docs/Execution plan.sqlplan
```

The reviewed daily production aggregation uses operators including:

- Nonclustered Index Scan
- Hash Match / aggregation
- Sort
- Parallelism

The plan also records SQL Server runtime information and memory/wait statistics.

The performance documentation explains:

- The query being tested
- The selected index
- Execution behavior
- Observed timing
- Optimization decision

See:

- [Performance Benchmark](docs/performance.md)
- [Execution Plan](docs/Execution%20plan.sqlplan)

---

## 14. Installation

### Requirements

Install:

- PHP 8.3+
- Composer
- Node.js and npm
- Microsoft SQL Server
- Git
- PHP SQL Server extensions:
  - `sqlsrv`
  - `pdo_sqlsrv`

### Clone

```bash
git clone https://github.com/Hammam-GNF/machine-monitoring.git
cd machine-monitoring
```

### Install PHP dependencies

```bash
composer install
```

### Install frontend dependencies

```bash
npm install
```

### Configure environment

Linux / macOS / Git Bash:

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

### Configure SQL Server

Update `.env`:

```env
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=machine_monitoring
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Make sure SQL Server is running and the configured database/user is accessible.

### Run migrations

```bash
php artisan migrate
```

### Seed demo data

```bash
php artisan db:seed
```

The demo seeder provides:

```text
1 admin user
1 viewer user
10 machines
3 sensors per machine
1,000 sensor data records
15 maintenance records
```

### Start the application

```bash
composer run dev
```

Application:

```text
http://localhost:8000
```

Detailed setup instructions:

- [Setup Guide](docs/setup.md)

---

## 15. Demo Accounts

The seeded demo accounts are intended for local/demo use only.

| Role | Email | Password |
|---|---|---|
| Admin | `admin@machine-monitoring.com` | `password` |
| Viewer | `viewer@machine-monitoring.com` | `password` |

Do not use these credentials in a production environment.

---

## 16. Testing

Run the complete test suite:

```bash
php artisan test
```

Run code formatting:

```bash
composer lint
```

Check formatting without modifying files:

```bash
composer lint:check
```

Run static analysis:

```bash
composer types:check
```

Run the complete project validation workflow:

```bash
composer test
```

The automated test suite covers:

- Authentication
- Role authorization
- Machine management
- Machine lifecycle
- Sensor management
- Sensor data API
- API validation
- Idempotency
- Maintenance detection
- Reporting
- Database relationships
- Database constraints
- Performance-related behavior

---

## 17. Recommended End-to-End Demo

For a technical showcase:

```text
1. Login as Admin
       ↓
2. Open Dashboard
       ↓
3. Open Machine Management
       ↓
4. Create a machine
       ↓
5. Verify the default virtual sensor
       ↓
6. Open Sensor Management
       ↓
7. Start the external IoT simulator
       ↓
8. Observe sensor data entering through the API
       ↓
9. Observe dashboard polling updates
       ↓
10. Demonstrate maintenance detection
       ↓
11. Open Production Report
       ↓
12. Show 100k+ data generator
       ↓
13. Show benchmark results
       ↓
14. Show SQL Server execution plan
       ↓
15. Explain architecture and database design
```

Detailed demo flow:

- [Demo Guide](docs/demo.md)

---

## 18. Project Structure

```text
machine-monitoring/
├── app/
│   ├── Actions/
│   ├── Console/
│   │   └── Commands/
│   │       ├── BenchmarkSensorData.php
│   │       ├── GenerateSensorData.php
│   │       └── SimulateSensorData.php
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Policies/
│   └── Services/
│
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── device-simulator/
│   ├── devices.json
│   └── simulator.php
│
├── docs/
│   ├── architecture.md
│   ├── architecture-diagram.mmd
│   ├── database.md
│   ├── database-erd.mmd
│   ├── demo.md
│   ├── Execution plan.sqlplan
│   ├── performance.md
│   ├── reporting.md
│   ├── requirements.md
│   └── setup.md
│
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│
├── routes/
│   ├── api.php
│   └── web.php
│
└── tests/
    ├── Feature/
    └── Unit/
```

---

## 19. Documentation

Additional technical documentation:

- [Requirements](docs/requirements.md)
- [Architecture](docs/architecture.md)
- [Database Design](docs/database.md)
- [Production Reporting](docs/reporting.md)
- [Performance Benchmark](docs/performance.md)
- [Setup Guide](docs/setup.md)
- [Demo Guide](docs/demo.md)
- [Architecture Diagram](docs/architecture-diagram.mmd)
- [Database ERD](docs/database-erd.mmd)
- [SQL Server Execution Plan](docs/Execution%20plan.sqlplan)

---

## 20. Development History

Development was organized into feature branches and incremental commits so that the Git history reflects the implementation progress.

Major stages include:

```text
Foundation
    ↓
Authentication & Authorization
    ↓
Database Foundation
    ↓
Machine & Sensor Management
    ↓
Sensor Data API
    ↓
External IoT Simulator
    ↓
Dashboard Monitoring
    ↓
Maintenance Detection
    ↓
Production Reporting
    ↓
Scalability / Benchmarking
    ↓
Machine Lifecycle Compliance
    ↓
Documentation & Polish
```

The project intentionally uses separate feature branches for major areas and multiple focused commits within feature branches.

---

## 21. Assumptions & Design Decisions

### Historical IoT data is preserved

Sensor data is treated as historical operational data and is not deleted when a machine becomes inactive.

### Machine deletion is modeled as deactivation

The application uses `is_active` for machine lifecycle management rather than destructive deletion.

### Duplicate IoT events are handled through idempotency

`event_id` is the idempotency key and is protected by both application logic and a database unique constraint.

### Shift is derived from timestamp

No `shifts` table is required for the current scope. Shift is derived from `recorded_at`.

### Output is period-based

The `output` field represents production generated during the corresponding sensor-data period, not a cumulative meter.

### Near-real-time monitoring uses polling

The dashboard uses periodic HTTP polling instead of WebSockets/SSE because the challenge allows polling and the current scope does not require persistent real-time connections.

### Large-data testing uses a dedicated generator

The 100,000+ record generator is separate from the normal application seeder so that demo seeding remains lightweight.

---

## 22. Current Scope Notes

This project prioritizes the challenge's required functional scope and scalability evidence.

The following are intentionally outside the current scope:

- Physical IoT hardware integration
- WebSocket/SSE infrastructure
- Excel/PDF export
- Pre-aggregated production summary tables
- Destructive machine deletion

Excel/PDF export is a bonus requirement and is therefore not required for challenge compliance.

The application does include query benchmarking and execution-plan evidence to make the scalability trade-offs explicit.

---

## 23. License

This project is developed as a technical challenge / portfolio project.
