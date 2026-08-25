# Machine Monitoring System - Setup Guide

## 1. Requirements

Before running the project, make sure the following are installed:

- PHP 8.3+
- Composer
- Node.js and npm
- Microsoft SQL Server
- Git

The project is developed as a Laravel application using SQL Server as its primary database.

## 2. Clone the Repository

```bash
git clone https://github.com/Hammam-GNF/machine-monitoring.git
cd machine-monitoring
```

## 3. Install Dependencies

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

## 4. Configure Environment

Copy the example environment file.

Linux/macOS/Git Bash:

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

## 5. Configure SQL Server

Update the database settings in `.env`.

Example:

```env
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=machine_monitoring
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Make sure the SQL Server instance is running and the configured database/user is accessible.

## 6. Run Migrations and Seed Demo Data

Run the database migrations:

```bash
php artisan migrate
```

For a fresh local/demo installation, seed the database:

```bash
php artisan db:seed
```

The current seeder creates:

- 1 admin user
- 1 viewer user
- 10 machines
- 3 sensors per machine
- 1,000 sensor data records
- 15 maintenance records

## 7. Demo Accounts

The seeded demo accounts are:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@machine-monitoring.com` | `password` |
| Viewer | `viewer@machine-monitoring.com` | `password` |

These credentials are intended for local/demo use only.

## 8. Start the Application

Run the Laravel development environment:

```bash
composer run dev
```

The application is available at:

```text
http://localhost:8000
```

The development command starts the required local development processes configured by the project.

## 9. Clear / Rebuild Laravel Caches

When changing configuration, routes, or Blade views, caches can be cleared or rebuilt with:

```bash
php artisan optimize:clear
```

To cache Blade templates:

```bash
php artisan view:cache
```

## 10. Run Tests

Run the complete test suite:

```bash
php artisan test
```

For a more verbose output:

```bash
php artisan test --verbose
```

## 11. Reset Local Demo Data

If you want to rebuild the local database from scratch:

```bash
php artisan migrate:fresh --seed
```

**Warning:** this deletes all existing application data in the configured database.

## 12. API Endpoint

The sensor data endpoint is:

```text
POST /api/sensor-data
```

Example request:

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

The API validates the incoming payload and machine/sensor relationship before storing the reading.

Sending the same `event_id` again returns the existing sensor data instead of creating another record.

## 13. Recommended Local Workflow

For normal development:

```text
1. Start SQL Server
       ↓
2. Start Laravel
       ↓
3. Open the dashboard
       ↓
4. Use seeded data or send sensor data through the API
       ↓
5. Verify machine status and maintenance detection
       ↓
6. Run the test suite
```
