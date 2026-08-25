# Machine Monitoring System - Demo Guide

This guide describes a simple end-to-end demonstration of the application's main functionality.

## 1. Prepare the Demo Environment

Make sure the application has been installed and seeded.

For a clean demo database:

```bash
php artisan migrate:fresh --seed
```

Start the application:

```bash
composer run dev
```

Open:

```text
http://localhost:8000
```

## 2. Login

Use one of the seeded demo accounts.

### Admin

```text
Email: admin@machine-monitoring.com
Password: password
```

The Admin role can access protected management actions.

### Viewer

```text
Email: viewer@machine-monitoring.com
Password: password
```

The Viewer role is intended for monitoring access and does not receive protected management permissions.

## 3. Dashboard

After login, open the dashboard.

The dashboard demonstrates:

- Total machines
- Active machines
- Machines requiring maintenance
- Current machine status
- Latest temperature readings
- Maintenance status
- Machine filtering

The dashboard uses the latest available sensor data for machine monitoring.

## 4. Machine Management

From the application navigation, open Machine Management.

Demonstrate:

1. Viewing the machine list.
2. Searching/filtering machines.
3. Opening a machine's details.
4. Creating a machine as Admin.
5. Updating a machine as Admin.
6. Verifying that protected actions are unavailable to Viewer.

## 5. Sensor Management

Open Sensor Management.

Demonstrate:

1. Viewing registered sensors.
2. Filtering sensors.
3. Viewing the machine associated with each sensor.
4. Creating a sensor as Admin.
5. Updating a sensor as Admin.
6. Verifying sensor status.

## 6. Sensor Data API

The application exposes:

```text
POST /api/sensor-data
```

Example PowerShell request:

```powershell
$body = @{
    event_id = [guid]::NewGuid().ToString()
    machine_id = 1
    sensor_id = 1
    status = "ON"
    temperature = 72.50
    output = 120
    recorded_at = "2026-08-25 10:00:00"
} | ConvertTo-Json

Invoke-RestMethod `
    -Uri "http://localhost:8000/api/sensor-data" `
    -Method Post `
    -ContentType "application/json" `
    -Body $body
```

A successful request creates a sensor reading.

## 7. Idempotency Demo

Send the same `event_id` more than once.

The first request creates the sensor data.

A repeated request with the same `event_id` should return the existing record instead of creating a duplicate.

This demonstrates the application's idempotency protection.

## 8. Maintenance Detection Demo

Maintenance detection is evaluated when sensor data is received.

The relevant conditions are:

```text
3 consecutive temperature readings > 80°C
```

or:

```text
Machine remains OFF > 30 minutes
```

When a condition is detected, the application can create a maintenance record with a reason such as:

```text
HIGH_TEMPERATURE
MACHINE_OFF
```

The dashboard then reflects the machine's maintenance state.

## 9. Production Reporting Demo

Open the Production Report.

Use the available date and shift filters to demonstrate:

- Production aggregation
- Daily reporting
- Monthly reporting
- Shift-based reporting
- Uptime / downtime information

Production is calculated from `sensor_data.output`.

Shift calculation is based on `recorded_at`:

```text
Shift 1: 06:00–14:00
Shift 2: 14:00–22:00
Shift 3: 22:00–06:00
```

## 10. Performance Demo

The project includes performance validation for the `sensor_data` table.

Run the test suite:

```bash
php artisan test
```

Performance-related tests validate behavior around:

- Large sensor datasets
- Query performance
- Pagination
- Database indexes

The project targets datasets of 100,000+ sensor records as part of its scalability consideration.

## 11. Suggested Technical Showcase Flow

For a short technical demonstration, use this sequence:

```text
Login as Admin
      ↓
Dashboard
      ↓
Machine Management
      ↓
Sensor Management
      ↓
Send sensor data through API
      ↓
Verify dashboard update
      ↓
Trigger / inspect maintenance condition
      ↓
Open Production Report
      ↓
Show tests and performance validation
      ↓
Explain architecture + database design
```

This sequence demonstrates the main application flow without requiring a long manual setup.

## 12. Demo Notes

The seeded data is intended to make the application immediately demonstrable after installation.

For a clean showcase, use:

```bash
php artisan migrate:fresh --seed
```

Do not use the seeded credentials in a production environment. They exist only for local/demo purposes.
