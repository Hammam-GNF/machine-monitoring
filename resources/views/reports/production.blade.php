<x-layouts::app :title="__('Production Report')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        @php
            $shiftLabels = [
                1 => 'Shift 1',
                2 => 'Shift 2',
                3 => 'Shift 3',
            ];

            $selectedShift = $filters['shift'] ?? null;
        @endphp

        <div>
            <flux:heading size="xl">
                Production Report
            </flux:heading>

            <flux:text class="mt-1">
                Monitor production output by period, shift, and machine.
            </flux:text>
        </div>

        {{-- Filters --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
            <form method="GET" action="{{ route('production-report.index') }}">
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <flux:field>
                        <flux:label>Period</flux:label>

                        <flux:select name="period">
                            <flux:select.option
                                value="day"
                                :selected="$period === 'day'"
                            >
                                Daily
                            </flux:select.option>

                            <flux:select.option
                                value="month"
                                :selected="$period === 'month'"
                            >
                                Monthly
                            </flux:select.option>
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Machine</flux:label>

                        <flux:select name="machine_id">
                            <flux:select.option value="">
                                All Machines
                            </flux:select.option>

                            @foreach ($machines as $machine)
                                <flux:select.option
                                    value="{{ $machine->id }}"
                                    :selected="($filters['machine_id'] ?? null) == $machine->id"
                                >
                                    {{ $machine->code }} — {{ $machine->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>

                    <flux:field>
                        <flux:label>Date From</flux:label>

                        <flux:input
                            type="date"
                            name="date_from"
                            value="{{ $filters['date_from'] ?? '' }}"
                        />
                    </flux:field>

                    <flux:field>
                        <flux:label>Date To</flux:label>

                        <flux:input
                            type="date"
                            name="date_to"
                            value="{{ $filters['date_to'] ?? '' }}"
                        />
                    </flux:field>

                    <flux:field>
                        <flux:label>Shift</flux:label>

                        <flux:select name="shift">
                            <flux:select.option value="">
                                All Shifts
                            </flux:select.option>

                            <flux:select.option
                                value="1"
                                :selected="($filters['shift'] ?? null) == 1"
                            >
                                Shift 1 (06:00–14:00)
                            </flux:select.option>

                            <flux:select.option
                                value="2"
                                :selected="($filters['shift'] ?? null) == 2"
                            >
                                Shift 2 (14:00–22:00)
                            </flux:select.option>

                            <flux:select.option
                                value="3"
                                :selected="($filters['shift'] ?? null) == 3"
                            >
                                Shift 3 (22:00–06:00)
                            </flux:select.option>
                        </flux:select>
                    </flux:field>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <flux:button
                        type="submit"
                        variant="primary"
                        icon="funnel"
                    >
                        Filter
                    </flux:button>

                    <flux:button
                        variant="ghost"
                        :href="route('production-report.index')"
                        wire:navigate
                    >
                        Reset
                    </flux:button>
                </div>
            </form>
        </div>

        {{-- Metrics --}}
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                <flux:text>Total Output</flux:text>

                <flux:heading size="lg" class="mt-1">
                    {{ number_format($metrics['total_output']) }}
                </flux:heading>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                <flux:text>Average Output / Hour</flux:text>

                <flux:heading size="lg" class="mt-1">
                    {{ number_format($metrics['average_output_per_hour'], 2) }}
                </flux:heading>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                <flux:text>Uptime</flux:text>

                <flux:heading size="lg" class="mt-1">
                    {{ number_format($metrics['uptime_percentage'], 2) }}%
                </flux:heading>
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-700 dark:bg-zinc-900">
                <flux:text>Downtime</flux:text>

                <flux:heading size="lg" class="mt-1">
                    {{ number_format($metrics['downtime_percentage'], 2) }}%
                </flux:heading>
            </div>
        </div>

        {{-- Report --}}
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 border-b border-neutral-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-neutral-700">
                <div>
                    <flux:heading size="sm">
                        {{ $period === 'month' ? 'Monthly' : 'Daily' }} Production
                    </flux:heading>

                    <flux:text class="mt-1">
                        Showing
                        {{ $report->firstItem() ?? 0 }}
                        –
                        {{ $report->lastItem() ?? 0 }}
                        of
                        {{ number_format($report->total()) }}
                        report rows
                    </flux:text>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-6 py-3 font-medium">
                                Machine
                            </th>

                            <th class="px-6 py-3 font-medium">
                                {{ $period === 'month' ? 'Month' : 'Date' }}
                            </th>

                            <th class="px-6 py-3 font-medium">
                                Shift
                            </th>

                            <th class="px-6 py-3 text-right font-medium">
                                Total Output
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse ($report as $row)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-800/50">
                                <td class="px-6 py-4">
                                    {{ $row->machine->code }}
                                    —
                                    {{ $row->machine->name }}
                                </td>

                                <td class="px-6 py-4">
                                    @if ($period === 'month')
                                        {{ sprintf('%04d-%02d', $row->year, $row->month) }}
                                    @else
                                        {{ $row->date }}
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    {{ $selectedShift
                                        ? $shiftLabels[$selectedShift]
                                        : 'All Shifts' }}
                                </td>

                                <td class="px-6 py-4 text-right font-medium">
                                    {{ number_format($row->total_output) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <flux:icon.chart-bar class="size-8 text-neutral-400" />

                                        <flux:heading size="sm">
                                            No production data found
                                        </flux:heading>

                                        <flux:text>
                                            Try adjusting the report filters.
                                        </flux:text>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($report->hasPages())
                <div class="border-t border-neutral-200 px-6 py-4 dark:border-neutral-700">
                    {{ $report->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
