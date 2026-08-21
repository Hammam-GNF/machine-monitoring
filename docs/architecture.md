# Machine Monitoring System - Architecture

## 1. Architecture Overview

Machine Monitoring System menggunakan Laravel sebagai application framework dan SQL Server sebagai relational database.

Architecture dibuat sederhana dan sesuai dengan kebutuhan MVP.

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
Service
      │
      ▼
Eloquent Model
      │
      ▼
SQL Server
```

Application Interface:
┌───────────────────────┐
│       Web Browser     │
│                       │
│ Dashboard             │
│ Machine Management    │
│ Sensor Management     │
│ Reports               │
└───────────┬───────────┘
            │
            ▼
┌───────────────────────┐
│        Laravel        │
│                       │
│ Authentication        │
│ Web Controllers       │
│ API Controllers       │
│ Form Requests         │
│ Services              │
│ Models                │
└───────────┬───────────┘
            │
            ▼
┌───────────────────────┐
│       SQL Server      │
│                       │
│ users                 │
│ machines              │
│ sensors               │
│ sensor_data           │
│ maintenance_records   │
└───────────────────────┘

## 2. Application Layers

HTTP Layer

HTTP layer menangani request dari browser dan IoT simulator.

Komponen utama:

Controllers
Form Requests
Middleware
Service Layer

Service layer menangani business logic yang tidak seharusnya ditempatkan langsung pada controller.

Contoh:

Sensor Data Processing
Maintenance Detection
Production Aggregation

Business logic akan ditambahkan sesuai kebutuhan masing-masing feature.

Model Layer

Eloquent Models bertanggung jawab terhadap:

Database representation
Relationships
Attribute handling
Query interaction

## 3. Recommended Directory Structure

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

Struktur akan berkembang mengikuti kebutuhan feature.

Tidak menggunakan repository pattern, DTO, atau abstraction layer tambahan tanpa kebutuhan nyata.

## 4. Authentication

Authentication menggunakan Laravel Fortify yang sudah tersedia pada project foundation.

Role disimpan langsung pada:

users.role

Role MVP:

admin
viewer

Authorization akan menggunakan Laravel authorization mechanism.

## 5. Machine Monitoring Flow

Machine
   │
   ├── Sensor
   │     │
   │     ▼
   │   Sensor Data
   │
   └── Maintenance Records

   Machine merupakan root entity untuk monitoring.

## 6. IoT Data Flow

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
SensorDataService
      │
      ├── Validate event
      ├── Validate machine
      ├── Validate sensor
      ├── Check idempotency
      └── Store sensor reading
             │
             ▼
        SQL Server

Maintenance detection akan menjadi bagian dari processing sensor data setelah foundation API tersedia.

## 7. Idempotency

Setiap sensor event memiliki:

event_id

event_id harus unique.

Jika event yang sama dikirim lebih dari satu kali, system tidak boleh membuat duplicate sensor data.

Database unique constraint menjadi salah satu lapisan perlindungan terhadap duplicate event.

## 8. Maintenance Detection Flow

Sensor Data
     │
     ▼
Evaluate Machine Condition
     │
     ├── 3 consecutive temperature > 80°C
     │          │
     │          ▼
     │    HIGH_TEMPERATURE
     │
     └── OFF > 30 minutes
                │
                ▼
          Maintenance Event

Maintenance record memiliki:

open
resolved

## 9. Reporting Flow

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

Shift tidak disimpan sebagai database entity.

Shift dihitung berdasarkan recorded_at.

## 10. Performance Considerations

Tabel utama untuk scalability adalah:

sensor_data

Target data:

100,000+

Query utama harus menggunakan index yang sesuai dengan pola akses data.

Index utama:

(machine_id, recorded_at)
(sensor_id, recorded_at)
(recorded_at)

Performance optimization akan dilakukan pada Day 6 setelah data dalam jumlah besar tersedia.

## 11. Testing Strategy

Testing dilakukan menggunakan Pest.

Testing mencakup:

Feature Tests
Authentication
Machine management
Sensor management
Sensor data API
Idempotency
Authorization
Maintenance detection
Reporting
Database Tests
Relationships
Constraints
Unique fields
Foreign keys
Performance Testing

Dilakukan secara terpisah untuk:

Large dataset
Query execution
Index effectiveness
Pagination


---