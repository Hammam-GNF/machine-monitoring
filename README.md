# Machine Monitoring System

Machine Monitoring System adalah aplikasi monitoring mesin berbasis Laravel yang menerima data sensor dari IoT simulator, menyimpan historical sensor data pada SQL Server, menampilkan kondisi mesin, mendeteksi kebutuhan maintenance, dan menyediakan laporan produksi.

## Tech Stack

- Laravel 13
- PHP 8.3+
- SQL Server
- Livewire
- Laravel Fortify
- Pest
- Vite

## MVP

- Authentication
- Admin / Viewer
- Machine Management
- Sensor Management
- IoT Sensor Data API
- Idempotency
- Dashboard
- Maintenance Detection
- Production Report
- Uptime / Downtime
- Performance Testing

## Documentation

- [Requirements](docs/requirements.md)
- [Architecture](docs/architecture.md)
- [Database Design](docs/database.md)

## Local Development

Clone repository:

```bash
git clone https://github.com/Hammam-GNF/machine-monitoring.git
cd machine-monitoring
```

Install PHP dependencies:
```bash
composer install
```

Install frontend dependencies:
```bash
npm install
```

Copy environment file:
```bash
cp .env.example .env
```

Generate application key:
```bash
php artisan key:generate
```

Configure SQL Server connection in .env.

Run migrations:
```bash
php artisan migrate
```
Run development server:
```bash
composer run dev
Testing
```

Run test suite:
```bash
php artisan test
```

Project Development

Development is divided into seven milestones:

Foundation
Master Data
IoT API
Simulator + Dashboard
Maintenance + Report
Scalability
Polish