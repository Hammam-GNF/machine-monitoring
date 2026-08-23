<x-layouts::app :title="__('Machines')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Machines</flux:heading>
                <flux:text class="mt-1">
                    Manage and monitor registered machines.
                </flux:text>
            </div>

            @if (auth()->user()->isAdmin())
                <flux:button
                    variant="primary"
                    :href="route('machines.create')"
                    wire:navigate
                >
                    Add Machine
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
                            <th class="px-6 py-3 font-medium">Location</th>
                            <th class="px-6 py-3 font-medium">Type</th>
                            <th class="px-6 py-3 font-medium">Installed</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                            <th class="px-6 py-3 font-medium">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse ($machines as $machine)
                            <tr>
                                <td class="px-6 py-4 font-medium">
                                    {{ $machine->code }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $machine->name }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $machine->location }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $machine->machine_type }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $machine->installed_at->format('Y-m-d') }}
                                </td>

                                <td class="px-6 py-4">
                                    @if ($machine->is_active)
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
                                        :href="route('machines.show', $machine)"
                                        wire:navigate
                                    >
                                        View
                                    </flux:button>

                                    @if (auth()->user()->isAdmin())
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            :href="route('machines.edit', $machine)"
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
                                    colspan="7"
                                    class="px-6 py-8 text-center text-neutral-500"
                                >
                                    No machines found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($machines->hasPages())
                <div class="border-t border-neutral-200 px-6 py-4 dark:border-neutral-700">
                    {{ $machines->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
