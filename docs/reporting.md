# Production Report

## Overview

The production report provides aggregated production output and operational metrics based on historical sensor data.

The report supports filtering by:

* Machine
* Date range
* Shift

Production output can be aggregated by:

* Day
* Month

The report also provides:

* Total output
* Average output per hour
* Uptime percentage
* Downtime percentage

## Production Aggregation

### Daily Production

Daily production is grouped by machine and the calendar date of `recorded_at`.

```text
machine_id + date(recorded_at)
```

The total output is calculated using:

```text
SUM(output)
```

For example, multiple sensor readings from the same machine on the same day are combined into a single report row.

### Monthly Production

Monthly production is grouped by machine, year, and month of `recorded_at`.

```text
machine_id + year(recorded_at) + month(recorded_at)
```

The total output is calculated using:

```text
SUM(output)
```

This allows production data to be viewed at either daily or monthly granularity.

## Filters

### Machine

The machine filter limits the report and metrics to sensor data belonging to the selected machine.

When no machine is selected, data from all machines is included.

### Date Range

The report supports optional `date_from` and `date_to` filters.

The filters are applied against the date portion of `recorded_at`:

```text
recorded_at >= date_from
recorded_at <= date_to
```

Both boundaries are inclusive.

### Shift

Production data can be filtered by one of three shifts:

| Shift   | Time        |
| ------- | ----------- |
| Shift 1 | 06:00–14:00 |
| Shift 2 | 14:00–22:00 |
| Shift 3 | 22:00–06:00 |

The shift filter is applied to the time portion of `recorded_at`.

Shift 3 crosses midnight and therefore includes records from:

```text
22:00–23:59:59
00:00–05:59:59
```

The selected shift acts as a filter rather than an additional report grouping dimension.

For example, a daily report filtered to Shift 1 represents the total production recorded during Shift 1 for that day.

## Production Metrics

### Total Output

Total output is the sum of the `output` values after all selected filters have been applied.

```text
Total Output = SUM(output)
```

### Average Output per Hour

The average output per hour is calculated from the total output and the number of distinct recorded hours.

```text
Average Output / Hour =
Total Output / Distinct Recorded Hours
```

Recorded hours are determined from the `recorded_at` timestamp after applying the same report filters.

At least one hour is used as the divisor when calculating the metric to prevent division by zero.

### Uptime Percentage

Uptime is based on the proportion of sensor readings whose status is `ON`.

```text
Uptime % =
(ON readings / Total readings) × 100
```

### Downtime Percentage

Downtime is calculated as the remaining percentage after uptime.

```text
Downtime % = 100 - Uptime %
```

If no sensor readings match the selected filters, all metrics return zero.

## Pagination

Daily and monthly production reports are returned as paginated results.

The default page size is:

```text
20 rows
```

The selected report filters are preserved when navigating between pages.

## Database Compatibility

The production report supports the application's SQL Server database while retaining database-specific expressions for other supported database drivers.

Date, month, year, and recorded-hour expressions are selected based on the active database driver.

## Example

Given the following readings:

| Time  | Output | Status |
| ----- | -----: | ------ |
| 06:00 |    100 | ON     |
| 07:00 |    200 | ON     |
| 08:00 |    300 | OFF    |
| 09:00 |    400 | ON     |

The resulting metrics are:

```text
Total Output           = 1,000
Average Output / Hour  = 250
Uptime                 = 75%
Downtime               = 25%
```

The same filters are applied consistently to both the production aggregation and the metric calculations.

## Design Notes

The production report separates aggregation from presentation.

`ProductionReportService` is responsible for:

* Applying report filters
* Aggregating production data
* Calculating production metrics
* Handling database-specific aggregation expressions

`ProductionReportController` is responsible for:

* Validating request filters
* Selecting the requested reporting period
* Passing the report data and metrics to the view

The Blade view is responsible only for presenting the filters, metrics, aggregated report, and pagination controls.
