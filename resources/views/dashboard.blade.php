<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">
                Machine Monitoring Dashboard
            </flux:heading>

            <flux:text class="mt-1">
                Monitor the current condition of registered machines.
            </flux:text>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
                <flux:text>Total Machines</flux:text>

                <flux:heading size="xl" class="mt-2">
                    {{ $totalMachines }}
                </flux:heading>
            </div>

            <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
                <flux:text>Active Machines</flux:text>

                <flux:heading size="xl" class="mt-2">
                    {{ $activeMachines }}
                </flux:heading>
            </div>

            <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
                <flux:text>Needs Maintenance</flux:text>

                <flux:heading size="xl" class="mt-2">
                    {{ $machinesNeedingMaintenance }}
                </flux:heading>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                <flux:heading size="lg">
                    Machine Monitoring
                </flux:heading>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="grid gap-4 border-b border-neutral-200 p-6 md:grid-cols-4 dark:border-neutral-700">
                <div>
                    <flux:input
                        name="search"
                        label="Search"
                        placeholder="Search machine code or name..."
                        value="{{ $search }}"
                    />
                </div>

                <div>
                    <flux:select name="status" label="Status">
                        <option value="" @selected($status === '')>All</option>
                        <option value="ON" @selected($status === 'ON')>ON</option>
                        <option value="OFF" @selected($status === 'OFF')>OFF</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </flux:select>
                </div>

                <div>
                    <flux:select name="maintenance" label="Maintenance">
                        <option value="" @selected($maintenance === '')>All</option>
                        <option value="normal" @selected($maintenance === 'normal')>Normal</option>
                        <option value="needs_maintenance" @selected($maintenance === 'needs_maintenance')>
                            Needs Maintenance
                        </option>
                    </flux:select>
                </div>

                <div class="flex items-end gap-2">
                    <flux:button type="submit" variant="primary">
                        Filter
                    </flux:button>

                    <flux:button href="{{ route('dashboard') }}" variant="ghost">
                        Reset
                    </flux:button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800/50">
                        <tr>
                            <th class="px-6 py-3 font-medium">Machine</th>
                            <th class="px-6 py-3 font-medium">Location</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                            <th class="px-6 py-3 font-medium">Temperature</th>
                            <th class="px-6 py-3 font-medium">Maintenance</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse ($machines as $machine)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-medium">
                                        {{ $machine->code }}
                                    </div>

                                    <div class="text-neutral-500">
                                        {{ $machine->name }}
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    {{ $machine->location }}
                                </td>

                                <td class="px-6 py-4">
                                    @if (! $machine->is_active)
                                        <flux:badge variant="danger">
                                            Inactive
                                        </flux:badge>
                                    @elseif (! $machine->latestSensorData)
                                        <flux:badge variant="warning">
                                            No Data
                                        </flux:badge>
                                    @elseif ($machine->latestSensorData->status === 'ON')
                                        <flux:badge variant="success">
                                            ON
                                        </flux:badge>
                                    @else
                                        <flux:badge variant="danger">
                                            OFF
                                        </flux:badge>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    @if ($machine->latestSensorData?->temperature !== null)
                                        {{ number_format((float) $machine->latestSensorData->temperature, 2) }} °C
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    @if ($machine->openMaintenanceRecord)
                                        <flux:badge variant="danger">
                                            Needs Maintenance
                                        </flux:badge>
                                    @else
                                        <flux:badge variant="success">
                                            Normal
                                        </flux:badge>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="5"
                                    class="px-6 py-8 text-center text-neutral-500"
                                >
                                    No machines found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
