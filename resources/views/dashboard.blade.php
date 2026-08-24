<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-8">

        {{-- Page Header --}}
        <div>
            <flux:heading size="xl">
                Machine Monitoring Dashboard
            </flux:heading>

            <flux:text class="mt-1">
                Monitor the current condition of registered machines.
            </flux:text>
        </div>

        {{-- Filters --}}
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
            <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                <flux:heading size="lg">
                    Filters
                </flux:heading>

                <flux:text class="mt-1">
                    Search and filter registered machines.
                </flux:text>
            </div>

            <form
                method="GET"
                action="{{ route('dashboard') }}"
                class="grid gap-4 p-6 md:grid-cols-4"
            >
                <flux:input
                    name="search"
                    label="Search"
                    placeholder="Machine code or name..."
                    value="{{ $search }}"
                />

                <flux:select name="status" label="Status">
                    <option value="" @selected($status === '')>
                        All
                    </option>

                    <option value="ON" @selected($status === 'ON')>
                        ON
                    </option>

                    <option value="OFF" @selected($status === 'OFF')>
                        OFF
                    </option>

                    <option value="inactive" @selected($status === 'inactive')>
                        Inactive
                    </option>
                </flux:select>

                <flux:select name="maintenance" label="Maintenance">
                    <option value="" @selected($maintenance === '')>
                        All
                    </option>

                    <option value="normal" @selected($maintenance === 'normal')>
                        Normal
                    </option>

                    <option
                        value="needs_maintenance"
                        @selected($maintenance === 'needs_maintenance')
                    >
                        Needs Maintenance
                    </option>
                </flux:select>

                <div class="flex items-end gap-2">
                    <flux:button
                        type="submit"
                        variant="primary"
                        icon="funnel"
                    >
                        Filter
                    </flux:button>

                    <flux:button
                        href="{{ route('dashboard') }}"
                        variant="ghost"
                    >
                        Reset
                    </flux:button>
                </div>
            </form>
        </div>

        {{-- Realtime Dashboard --}}
        <div id="dashboard-realtime" class="flex flex-col gap-6">

            {{-- Statistics --}}
            <div class="grid gap-4 md:grid-cols-3">

                <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                    <flux:text>
                        Total Machines
                    </flux:text>

                    <flux:heading size="xl" class="mt-2">
                        {{ $totalMachines }}
                    </flux:heading>

                    <flux:text class="mt-1 text-sm">
                        Registered machines
                    </flux:text>
                </div>

                <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                    <flux:text>
                        Active Machines
                    </flux:text>

                    <flux:heading size="xl" class="mt-2">
                        {{ $activeMachines }}
                    </flux:heading>

                    <flux:text class="mt-1 text-sm">
                        Currently active
                    </flux:text>
                </div>

                <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                    <flux:text>
                        Needs Maintenance
                    </flux:text>

                    <flux:heading size="xl" class="mt-2">
                        {{ $machinesNeedingMaintenance }}
                    </flux:heading>

                    <flux:text class="mt-1 text-sm">
                        Open maintenance records
                    </flux:text>
                </div>

            </div>

            {{-- Machine Monitoring --}}
            <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-900">

                <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                    <flux:heading size="lg">
                        Machine Monitoring
                    </flux:heading>

                    <flux:text class="mt-1">
                        Current machine status and sensor readings.
                    </flux:text>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-800/50">
                            <tr>
                                <th class="whitespace-nowrap px-6 py-3 font-medium text-neutral-600 dark:text-neutral-300">
                                    Machine
                                </th>

                                <th class="whitespace-nowrap px-6 py-3 font-medium text-neutral-600 dark:text-neutral-300">
                                    Location
                                </th>

                                <th class="whitespace-nowrap px-6 py-3 font-medium text-neutral-600 dark:text-neutral-300">
                                    Status
                                </th>

                                <th class="whitespace-nowrap px-6 py-3 font-medium text-neutral-600 dark:text-neutral-300">
                                    Temperature
                                </th>

                                <th class="whitespace-nowrap px-6 py-3 font-medium text-neutral-600 dark:text-neutral-300">
                                    Maintenance
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                            @forelse ($machines as $machine)
                                <tr class="transition-colors hover:bg-neutral-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-neutral-900 dark:text-white">
                                            {{ $machine->code }}
                                        </div>

                                        <div class="mt-0.5 text-neutral-500 dark:text-neutral-400">
                                            {{ $machine->name }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-neutral-700 dark:text-neutral-300">
                                        {{ $machine->location }}
                                    </td>

                                    <td class="whitespace-nowrap px-6 py-4">
                                        @if (! $machine->is_active)
                                            <flux:badge variant="danger" size="sm">
                                                Inactive
                                            </flux:badge>
                                        @elseif (! $machine->latestSensorData)
                                            <flux:badge variant="warning" size="sm">
                                                No Data
                                            </flux:badge>
                                        @elseif ($machine->latestSensorData->status === 'ON')
                                            <flux:badge variant="success" size="sm">
                                                ON
                                            </flux:badge>
                                        @else
                                            <flux:badge variant="danger" size="sm">
                                                OFF
                                            </flux:badge>
                                        @endif
                                    </td>

                                    <td class="whitespace-nowrap px-6 py-4 font-medium text-neutral-700 dark:text-neutral-300">
                                        @if ($machine->latestSensorData?->temperature !== null)
                                            {{ number_format((float) $machine->latestSensorData->temperature, 2) }} °C
                                        @else
                                            <span class="font-normal text-neutral-400">
                                                -
                                            </span>
                                        @endif
                                    </td>

                                    <td class="whitespace-nowrap px-6 py-4">
                                        @if ($machine->openMaintenanceRecord)
                                            <flux:badge variant="danger" size="sm">
                                                Needs Maintenance
                                            </flux:badge>
                                        @else
                                            <flux:badge variant="success" size="sm">
                                                Normal
                                            </flux:badge>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <flux:icon.chart-bar class="size-8 text-neutral-400" />

                                            <flux:heading size="sm">
                                                No machines found
                                            </flux:heading>

                                            <flux:text>
                                                No machines match the current filters.
                                            </flux:text>

                                            @if ($search || $status || $maintenance)
                                                <flux:button
                                                    size="sm"
                                                    variant="ghost"
                                                    href="{{ route('dashboard') }}"
                                                    class="mt-2"
                                                >
                                                    Clear Filters
                                                </flux:button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
        @include('partials.scripts')
    @endpush
</x-layouts::app>
