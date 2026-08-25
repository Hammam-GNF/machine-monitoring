<x-layouts::app :title="__('Sensors')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">Sensors</flux:heading>

                <flux:text class="mt-1">
                    Manage sensors registered on machines.
                </flux:text>
            </div>

            @if (auth()->user()->isAdmin())
                <flux:button
                    variant="primary"
                    icon="plus"
                    :href="route('sensors.create')"
                    wire:navigate
                >
                    Add Sensor
                </flux:button>
            @endif
        </div>

        @if (session('success'))
            <flux:callout variant="success">
                {{ session('success') }}
            </flux:callout>
        @endif

        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-zinc-800/50">
                        <tr>
                            <th class="whitespace-nowrap px-6 py-3 font-medium text-neutral-600 dark:text-neutral-300">
                                Code
                            </th>
                            <th class="whitespace-nowrap px-6 py-3 font-medium text-neutral-600 dark:text-neutral-300">
                                Name
                            </th>
                            <th class="whitespace-nowrap px-6 py-3 font-medium text-neutral-600 dark:text-neutral-300">
                                Machine
                            </th>
                            <th class="whitespace-nowrap px-6 py-3 font-medium text-neutral-600 dark:text-neutral-300">
                                Type
                            </th>
                            <th class="whitespace-nowrap px-6 py-3 font-medium text-neutral-600 dark:text-neutral-300">
                                Status
                            </th>
                            <th class="whitespace-nowrap px-6 py-3 text-right font-medium text-neutral-600 dark:text-neutral-300">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse ($sensors as $sensor)
                            <tr class="transition-colors hover:bg-neutral-50 dark:hover:bg-zinc-800/50">
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-neutral-900 dark:text-white">
                                    {{ $sensor->code }}
                                </td>

                                <td class="px-6 py-4 text-neutral-700 dark:text-neutral-300">
                                    {{ $sensor->name }}
                                </td>

                                <td class="px-6 py-4 text-neutral-700 dark:text-neutral-300">
                                    {{ $sensor->machine->name }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-neutral-700 dark:text-neutral-300">
                                    {{ ucfirst($sensor->type) }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4">
                                    @if ($sensor->is_active)
                                        <flux:badge variant="success" size="sm">
                                            Active
                                        </flux:badge>
                                    @else
                                        <flux:badge variant="danger" size="sm">
                                            Inactive
                                        </flux:badge>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="eye"
                                            :href="route('sensors.show', $sensor)"
                                            wire:navigate
                                        >
                                            View
                                        </flux:button>

                                        @if (auth()->user()->isAdmin())
                                            <flux:button
                                                size="sm"
                                                variant="ghost"
                                                icon="pencil-square"
                                                :href="route('sensors.edit', $sensor)"
                                                wire:navigate
                                            >
                                                Edit
                                            </flux:button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <flux:icon.cpu-chip class="size-8 text-neutral-400" />

                                        <flux:heading size="sm">
                                            No sensors found
                                        </flux:heading>

                                        <flux:text>
                                            There are no sensors registered yet.
                                        </flux:text>

                                        @if (auth()->user()->isAdmin())
                                            <flux:button
                                                size="sm"
                                                variant="ghost"
                                                icon="plus"
                                                :href="route('sensors.create')"
                                                wire:navigate
                                                class="mt-2"
                                            >
                                                Add Sensor
                                            </flux:button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($sensors->hasPages())
                <div class="border-t border-neutral-200 px-6 py-4 dark:border-neutral-700">
                    {{ $sensors->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
