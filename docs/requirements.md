# Machine Monitoring System - Requirements

## 1. Project Overview

Machine Monitoring System adalah aplikasi monitoring mesin berbasis Laravel yang menerima data sensor dari IoT simulator melalui API, menyimpan historical sensor data pada SQL Server, menampilkan kondisi mesin melalui dashboard, mendeteksi kebutuhan maintenance, dan menyediakan laporan produksi.

## 2. MVP Scope

MVP terdiri dari:

- Authentication
- Role Admin dan Viewer
- Machine Management
- Sensor Management
- IoT Simulator
- Sensor Data API
- Sensor Data Validation
- Idempotency
- Dashboard
- Machine Status Monitoring
- Search
- Filter
- Polling
- Maintenance Detection
- Production Report
- Date Filter
- Shift-based Report
- Day/Month Aggregation
- Uptime/Downtime
- Performance Testing

### Application Flow

```text
Authentication
      │
      ▼
Machine Management
Machine + Sensor
      │
      ▼
IoT Simulator
      │
      │ POST
      ▼
Sensor Data API
Validation + Idempotency
      │
      ▼
SQL Server
sensor_data
      │
      ├───────────────┐
      ▼               ▼
Dashboard          Reports
      │
      ▼
Maintenance Detection
```

## 3. Authentication

System memiliki dua role:

admin
viewer
Admin

Admin memiliki akses untuk mengelola master data dan fungsi administratif.

Viewer

Viewer memiliki akses read-only terhadap data monitoring.

## 4. Machine Management

Setiap machine memiliki:

Unique code
Name
Location / line
Machine type
Installation date
Active status

Machine dapat memiliki banyak sensor.

## 5. Sensor Management

Setiap sensor memiliki:

Machine
Unique code per machine
Name
Type
Active status

Sensor hanya dapat dimiliki oleh satu machine.

## 6. Sensor Data API

IoT simulator mengirim data sensor melalui:

POST /api/sensor-data

API harus menangani:

Request validation
Sensor validation
Machine validation
Sensor data storage
Idempotency
Error response

## 7. Sensor Data

Sensor reading terdiri dari:

Event ID
Machine
Sensor
Status
Temperature
Output
Recorded time
Received time
Output

output merupakan jumlah unit produksi yang dihasilkan pada periode sensor tersebut.

Output bukan cumulative meter.

Contoh simulator dengan interval 5 menit:

18:00 → 120
18:05 → 115
18:10 → 125

Total:

360 units

## 8. Maintenance Detection

System memiliki dua maintenance rules.

Rule 1 — High Temperature

Jika terdapat 3 consecutive sensor readings dengan:

temperature > 80°C

maka system membuat maintenance event.

Contoh:

10:00 → ON → 70°C
10:05 → ON → 85°C
10:10 → ON → 87°C

Pada reading ketiga yang memenuhi kondisi, system membuat:

reason: HIGH_TEMPERATURE
status: open
Rule 2 — Machine OFF

Jika machine berada dalam kondisi:

OFF > 30 minutes

maka system membuat maintenance event.

Maintenance Status

Maintenance record memiliki status:

open
resolved

## 9. Shift

System menggunakan tiga shift:

| Shift   | Time          |
| ------- | ------------- |
| Shift 1 | 06:00 – 14:00 |
| Shift 2 | 14:00 – 22:00 |
| Shift 3 | 22:00 – 06:00 |

Tidak diperlukan tabel shifts.

Shift ditentukan berdasarkan recorded_at.

Contoh:

2026-08-21 07:30 → Shift 1
2026-08-21 18:30 → Shift 2
2026-08-21 23:30 → Shift 3

## 10. Dashboard

Dashboard menampilkan kondisi machine saat ini.

Informasi utama:

Machine
Current status
Current temperature
Maintenance indicator

Contoh:

MC-001
ON
91°C


⚠ NEEDS MAINTENANCE

Dashboard juga menyediakan:

Search
Filter
Polling

## 11. Production Report

Report menggunakan data dari:

sensor_data.output

Report mendukung:

Date filter
Shift
Day
Month
Output aggregation
Uptime
Downtime

## 12. Performance Requirement

Historical sensor data ditargetkan mencapai:

100,000+ records

System harus diuji terhadap:

Indexing
Query optimization
Execution plan
Pagination
Slow query detection
Query performance

## 13. MVP Data Volume

| Table                 | Purpose             | Expected Volume |
| --------------------- | ------------------- | --------------: |
| `users`               | Authentication      |           Small |
| `machines`            | Machine master data |            Tens |
| `sensors`             | Virtual sensors     | Tens / hundreds |
| `sensor_data`         | Historical IoT data |           100k+ |
| `maintenance_records` | Maintenance events  |  Small / medium |


---