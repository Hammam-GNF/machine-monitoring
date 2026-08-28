<x-layouts::app :title="__('Machines')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">Machines</flux:heading>

                <flux:text class="mt-1">
                    Manage and monitor registered machines.
                </flux:text>
            </div>

            @if (auth()->user()->isAdmin())
                <flux:button
                    variant="primary"
                    icon="plus"
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
                                Location
                            </th>
                            <th class="whitespace-nowrap px-6 py-3 font-medium text-neutral-600 dark:text-neutral-300">
                                Type
                            </th>
                            <th class="whitespace-nowrap px-6 py-3 font-medium text-neutral-600 dark:text-neutral-300">
                                Installed
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
                        @forelse ($machines as $machine)
                            <tr class="transition-colors hover:bg-neutral-50 dark:hover:bg-zinc-800/50">
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-neutral-900 dark:text-white">
                                    {{ $machine->code }}
                                </td>

                                <td class="px-6 py-4 text-neutral-700 dark:text-neutral-300">
                                    {{ $machine->name }}
                                </td>

                                <td class="px-6 py-4 text-neutral-700 dark:text-neutral-300">
                                    {{ $machine->location }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-neutral-700 dark:text-neutral-300">
                                    {{ $machine->machine_type }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-neutral-700 dark:text-neutral-300">
                                    {{ $machine->installed_at->format('Y-m-d') }}
                                </td>

                                <td class="whitespace-nowrap px-6 py-4">
                                    @if ($machine->is_active)
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
                                            :href="route('machines.show', $machine)"
                                            wire:navigate
                                        >
                                            View
                                        </flux:button>

                                        @if (auth()->user()->isAdmin())
                                            <flux:button
                                                size="sm"
                                                variant="ghost"
                                                icon="pencil-square"
                                                :href="route('machines.edit', $machine)"
                                                wire:navigate
                                            >
                                                Edit
                                            </flux:button>
                                        @endif

                                        @if (auth()->user()->isAdmin())
                                            @if ($machine->is_active)
                                                <form
                                                    method="POST"
                                                    action="{{ route('machines.deactivate', $machine) }}"
                                                    class="inline"
                                                >
                                                    @csrf

                                                    <flux:button
                                                        type="submit"
                                                        size="sm"
                                                        variant="ghost"
                                                        icon="pause"
                                                    >
                                                        Deactivate
                                                    </flux:button>
                                                </form>
                                            @else
                                                <form
                                                    method="POST"
                                                    action="{{ route('machines.activate', $machine) }}"
                                                    class="inline"
                                                >
                                                    @csrf

                                                    <flux:button
                                                        type="submit"
                                                        size="sm"
                                                        variant="ghost"
                                                        icon="play"
                                                    >
                                                        Activate
                                                    </flux:button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <flux:icon.building-office-2 class="size-8 text-neutral-400" />

                                        <flux:heading size="sm">
                                            No machines found
                                        </flux:heading>

                                        <flux:text>
                                            There are no machines registered yet.
                                        </flux:text>

                                        @if (auth()->user()->isAdmin())
                                            <flux:button
                                                size="sm"
                                                variant="ghost"
                                                icon="plus"
                                                :href="route('machines.create')"
                                                wire:navigate
                                                class="mt-2"
                                            >
                                                Add Machine
                                            </flux:button>
                                        @endif
                                    </div>
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
