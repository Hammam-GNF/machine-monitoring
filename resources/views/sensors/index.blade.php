<x-layouts::app :title="__('Sensors')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Sensors</flux:heading>

                <flux:text class="mt-1">
                    Manage sensors registered on machines.
                </flux:text>
            </div>

            @if (auth()->user()->isAdmin())
                <flux:button
                    variant="primary"
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

        <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800/50">
                        <tr>
                            <th class="px-6 py-3 font-medium">Code</th>
                            <th class="px-6 py-3 font-medium">Name</th>
                            <th class="px-6 py-3 font-medium">Machine</th>
                            <th class="px-6 py-3 font-medium">Type</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                            <th class="px-6 py-3 font-medium">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse ($sensors as $sensor)
                            <tr>
                                <td class="px-6 py-4 font-medium">
                                    {{ $sensor->code }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $sensor->name }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $sensor->machine->name }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ ucfirst($sensor->type) }}
                                </td>

                                <td class="px-6 py-4">
                                    @if ($sensor->is_active)
                                        <flux:badge variant="success">
                                            Active
                                        </flux:badge>
                                    @else
                                        <flux:badge variant="danger">
                                            Inactive
                                        </flux:badge>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        :href="route('sensors.show', $sensor)"
                                        wire:navigate
                                    >
                                        View
                                    </flux:button>

                                    @if (auth()->user()->isAdmin())
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            :href="route('sensors.edit', $sensor)"
                                            wire:navigate
                                        >
                                            Edit
                                        </flux:button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="px-6 py-8 text-center text-neutral-500"
                                >
                                    No sensors found.
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
