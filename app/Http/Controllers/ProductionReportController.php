<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Services\ProductionReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionReportController extends Controller
{
    public function __construct(
        private readonly ProductionReportService $productionReportService
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'period' => ['nullable', 'in:day,month'],
            'machine_id' => ['nullable', 'integer', 'exists:machines,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'shift' => ['nullable', 'integer', 'in:1,2,3'],
        ]);

        $period = $filters['period'] ?? 'day';

        $params = [
            'dateFrom' => $filters['date_from'] ?? null,
            'dateTo' => $filters['date_to'] ?? null,
            'shift' => $filters['shift'] ?? null,
            'machineId' => $filters['machine_id'] ?? null,
        ];

        $report = $period === 'month'
            ? $this->productionReportService->aggregateByMonth(...$params)
            : $this->productionReportService->aggregateByDay(...$params);

        return view('reports.production', [
            'report' => $report,
            'metrics' => $this->productionReportService->metrics(...$params),
            'machines' => Machine::query()
                ->orderBy('name')
                ->get(),
            'filters' => $filters,
            'period' => $period,
        ]);
    }
}
