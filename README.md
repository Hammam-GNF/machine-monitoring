# Machine Monitoring System

Machine Monitoring System adalah aplikasi monitoring mesin berbasis Laravel yang menerima data sensor dari IoT simulator, menyimpan historical sensor data pada SQL Server, menampilkan kondisi mesin, mendeteksi kebutuhan maintenance, dan menyediakan laporan produksi.

Project ini dibuat sebagai technical showcase untuk menunjukkan implementasi Laravel pada aplikasi monitoring berbasis data sensor dengan fokus pada validation, authorization, idempotency, database performance, automated testing, dan responsive web interface.

## Features

### Authentication & Authorization

- User authentication
- Admin and Viewer roles
- Role-based authorization
- Protected management actions

### Machine Management

- Create, view, update, and manage machines
- Machine status tracking
- Machine location and type information
- Machine installation date

### Sensor Management

- Register sensors to machines
- Sensor type management
- Active / inactive sensor status
- Sensor-machine relationship

### IoT Sensor Data API

- Receive sensor readings through HTTP API
- Validate incoming sensor data
- Validate machine and sensor relationships
- Event-based sensor data processing
- Idempotency protection using `event_id`

### Machine Monitoring

- Current machine status
- Latest sensor readings
- Temperature monitoring
- Machine filtering
- Dashboard updates based on incoming sensor data

### Maintenance Detection

The system detects machine conditions that require maintenance based on sensor data, including:

- Three consecutive temperature readings above 80°C
- Machine remaining OFF for more than 30 minutes

Maintenance records support:

- Open
- Resolved

### Production Reporting

- Production data aggregation
- Date-based reporting
- Shift-based reporting
- Daily reporting
- Monthly reporting
- Uptime / downtime calculation

### Performance

The application includes performance validation for large sensor datasets.

Performance considerations include:

- Database indexes for sensor data queries
- Pagination
- Query optimization
- Large dataset generation
- Query benchmarking
- Database index effectiveness

The primary scalability concern is the `sensor_data` table because sensor readings continuously accumulate over time.

## Tech Stack

| Technology | Purpose |
| --- | --- |
| Laravel 13 | Application framework |
| PHP 8.3+ | Backend runtime |
| SQL Server | Relational database |
| Livewire 4 | Reactive UI |
| Flux | UI component library |
| Laravel Fortify | Authentication |
| Pest 4 | Automated testing |
| Larastan | Static analysis |
| Vite | Frontend asset bundling |

## System Workflow

### Monitoring Workflow

```text
Machine
   │
   ├── Sensor
   │     │
   │     ▼
   │   Sensor Data
   │
   └── Maintenance Records
```

A machine can have multiple sensors, while sensor readings are stored as historical sensor data.

### IoT Data Workflow

```text
IoT Simulator
      │
      │ HTTP POST
      ▼
Sensor Data API
      │
      ▼
Validation
      │
      ▼
Sensor Data Processing
      │
      ├── Validate Event
      ├── Validate Machine
      ├── Validate Sensor
      ├── Check Idempotency
      └── Store Reading
             │
             ▼
         SQL Server
```

### Dashboard Workflow

```text
Sensor Data
     │
     ▼
Machine Monitoring
     │
     ├── Machine Status
     ├── Temperature
     └── Maintenance Status
             │
             ▼
          Dashboard
```

## Requirements

Before running the application, make sure the following are installed:

- PHP 8.3 or higher
- Composer
- Node.js and npm
- SQL Server
- Git

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/Hammam-GNF/machine-monitoring.git
cd machine-monitoring
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Frontend Dependencies

```bash
npm install
```

### 4. Configure Environment

Copy the example environment file.

Linux / macOS:

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

### 5. Configure SQL Server

Update the database configuration in `.env`.

Example:

```env
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=machine_monitoring
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Use the SQL Server credentials configured in your local environment.

### 6. Run Database Migrations

```bash
php artisan migrate
```

If the project provides seed data and you want to populate the database:

```bash
php artisan db:seed
```

### 7. Start the Application

Run the Laravel development environment:

```bash
composer run dev
```

The application will be available at:

```text
http://localhost:8000
```

The development command starts the Laravel server, queue listener, and Vite development server.

## Testing

Run the complete test suite:

```bash
php artisan test
```

For the project's complete validation workflow, including formatting and static analysis:

```bash
composer test
```

The test suite covers areas including:

- Authentication
- Machine management
- Sensor management
- Sensor data API
- Idempotency
- Authorization
- Maintenance detection
- Reporting
- Database relationships
- Database constraints
- Performance-related behavior

## Code Quality

The project uses Laravel Pint for code formatting and Larastan for static analysis.

Check code formatting:

```bash
composer lint:check
```

Run static analysis:

```bash
composer types:check
```

Automatically format the codebase:

```bash
composer lint
```

## Project Structure

The project follows a conventional Laravel structure with application actions, services, policies, and request validation where they provide meaningful separation of responsibilities.

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

The application intentionally avoids unnecessary abstraction such as repository or DTO layers when they do not provide meaningful value for the current scope.

## API

The application exposes an endpoint for receiving sensor readings:

```http
POST /api/sensor-data
```

The endpoint accepts sensor data from the IoT simulator and processes the reading through validation and idempotency checks before storing it in the database.

Example request structure:

```json
{
    "event_id": "event-001",
    "machine_code": "MCH-001",
    "sensor_code": "SNS-001",
    "status": "ON",
    "temperature": 72.5
}
```

The exact validation rules and request structure are defined by the API request validation in the application source.

## Performance Testing

Sensor data is expected to become the largest dataset in the application.

Performance testing focuses on:

- Large sensor data generation
- Query benchmarking
- Index optimization
- Pagination
- Query execution performance

The performance work is intended to demonstrate how database indexing and query design affect application performance as historical sensor data grows.

## Documentation

More detailed documentation is available in the `docs` directory:

- [Requirements](docs/requirements.md)
- [Architecture](docs/architecture.md)
- [Database Design](docs/database.md)

## Development Milestones

Development was divided into seven milestones.

### 1. Foundation

- Application setup
- Authentication
- Authorization
- Database foundation

### 2. Master Data

- Machine management
- Sensor management

### 3. IoT API

- Sensor data API
- Request validation
- Machine and sensor validation
- Idempotency

### 4. Simulator + Dashboard

- IoT simulator
- Machine monitoring
- Dashboard
- Sensor data updates

### 5. Maintenance + Report

- Maintenance detection
- Maintenance records
- Production reporting
- Uptime / downtime

### 6. Scalability

- Large dataset generation
- Query benchmarking
- Index optimization
- Pagination performance

### 7. Polish

- Application navigation
- Dashboard UI
- Machine management UI
- Sensor management UI
- Documentation
- Release preparation
