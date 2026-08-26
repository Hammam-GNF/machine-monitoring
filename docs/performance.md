# Sensor Data Performance Benchmark

## Dataset

The scalability benchmark was executed against:

- Database: Microsoft SQL Server
- Sensor data records: 101,000
- Machines: 10
- Historical data range: 2026-07-26 to 2026-08-25

The large dataset was generated using the dedicated sensor data generator command instead of modifying the default application seeder.

## Existing Indexes

The `sensor_data` table contains the following relevant indexes:

| Index | Columns | Purpose |
|---|---|---|
| `sensor_data_machine_id_recorded_at_index` | `machine_id, recorded_at` | Latest sensor data per machine |
| `sensor_data_recorded_at_index` | `recorded_at` | Time-based filtering |
| `sensor_data_recorded_at_output_index` | `recorded_at` + included `output` | Production aggregation |
| `sensor_data_sensor_id_recorded_at_index` | `sensor_id, recorded_at` | Sensor/time-based queries |
| `sensor_data_event_id_unique` | `event_id` | Event idempotency |

## Benchmark Results

The benchmark was executed against 101,000 sensor data records.

| Query | Average | Min | Max | Result |
|---|---:|---:|---:|---:|
| Latest sensor data per machine | 4,035.98 ms | 396.15 ms | 8,351.08 ms | 10 rows |
| Production aggregation by day | 22,259.28 ms | 45.42 ms | 55,737.51 ms | 31 rows |
| Production aggregation by month | 54.81 ms | 46.29 ms | 71.64 ms | 2 rows |
| Production aggregation by date range | 22.45 ms | 12.73 ms | 41.29 ms | 7 rows |
| Production aggregation by date range and shift | 166.83 ms | 20.88 ms | 458.63 ms | 7 rows |

> Benchmark timings were collected on a local development environment. The machine was under high memory utilization during testing, so elapsed time showed significant variation between runs. The results should therefore be treated as environment-specific benchmark results rather than a production SLA.

## Execution Plan

Detailed SQL Server execution-plan evidence is stored separately in the repository as SQL files.

### Production aggregation by day

Query:

```sql
SELECT
    CAST(recorded_at AS date) AS date,
    SUM(output) AS total_output
FROM sensor_data
GROUP BY CAST(recorded_at AS date)
ORDER BY date;
```

The actual execution plan uses:

- Nonclustered Index Scan
- Compute Scalar
- Repartition Streams
- Hash Match / aggregation operators
- Sort

The relevant production index was used:

```text
sensor_data_recorded_at_output_index
```

SQL Server statistics:

```text
Table 'sensor_data'.
Scan count 9
logical reads 2000
physical reads 3

CPU time = 202 ms
elapsed time = 7960 ms
```

The query processes the full historical dataset because the report aggregates production across all available dates.

### Latest sensor data per machine

Query:

```sql
SELECT
    machine_id,
    MAX(recorded_at) AS latest_recorded_at
FROM sensor_data
GROUP BY machine_id;
```

The actual execution plan uses:

- Nonclustered Index Scan
- Stream Aggregate

The relevant index is:

```text
sensor_data_machine_id_recorded_at_index
```

SQL Server statistics:

```text
Table 'sensor_data'.
Scan count 1
logical reads 527
physical reads 3

CPU time = 62 ms
elapsed time = 114 ms
```

## Optimization Decision

No additional indexes were added after reviewing the actual execution plans.

The existing indexes are already being selected by SQL Server for the tested queries.

Adding another index for the daily aggregation was not considered justified because the query intentionally aggregates the complete historical dataset and therefore still needs to process the available records.

The latest-sensor query already benefits from the composite:

```text
(machine_id, recorded_at)
```

index.

The benchmark also showed substantial elapsed-time variation on the local development machine while CPU utilization for the tested queries remained relatively low. Therefore, the measured latency should not be interpreted as evidence that additional database indexes are required.

## Conclusion

The scalability requirement is satisfied by:

1. Supporting a dataset above 100,000 historical sensor records.
2. Providing a dedicated large-data generator.
3. Benchmarking representative sensor-data queries.
4. Verifying existing database indexes.
5. Reviewing actual SQL Server execution plans.
6. Documenting the observed performance and optimization decision.

No unnecessary indexes were introduced solely for the purpose of the technical challenge.
