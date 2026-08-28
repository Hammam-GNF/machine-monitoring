<x-layouts::app :title="$machine->name">
    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">
                    {{ $machine->name }}
                </flux:heading>

                <flux:text class="mt-1">
                    {{ $machine->code }}
                </flux:text>
            </div>

            @if ($machine->is_active)
                <flux:badge variant="success">
                    Active
                </flux:badge>
            @else
                <flux:badge variant="danger">
                    Inactive
                </flux:badge>
            @endif
        </div>

        @if (session('success'))
            <flux:callout variant="success">
                {{ session('success') }}
            </flux:callout>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
                <flux:text size="sm">Machine Code</flux:text>
                <flux:heading size="lg" class="mt-1">
                    {{ $machine->code }}
                </flux:heading>
            </div>

            <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
                <flux:text size="sm">Machine Type</flux:text>
                <flux:heading size="lg" class="mt-1">
                    {{ $machine->machine_type }}
                </flux:heading>
            </div>

            <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
                <flux:text size="sm">Location / Line</flux:text>
                <flux:heading size="lg" class="mt-1">
                    {{ $machine->location }}
                </flux:heading>
            </div>

            <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
                <flux:text size="sm">Installation Date</flux:text>
                <flux:heading size="lg" class="mt-1">
                    {{ $machine->installed_at->format('Y-m-d') }}
                </flux:heading>
            </div>
        </div>

        <div class="flex gap-3">
            <flux:button
                variant="ghost"
                :href="route('machines.index')"
                wire:navigate
            >
                Back
            </flux:button>

            @if (auth()->user()->isAdmin())
                <flux:button
                    variant="primary"
                    :href="route('machines.edit', $machine)"
                    wire:navigate
                >
                    Edit Machine
                </flux:button>

                @if ($machine->is_active)
                    <form
                        method="POST"
                        action="{{ route('machines.deactivate', $machine) }}"
                        class="inline"
                    >
                        @csrf

                        <flux:button
                            type="submit"
                            variant="danger"
                        >
                            Deactivate Machine
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
                            variant="primary"
                        >
                            Activate Machine
                        </flux:button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</x-layouts::app>
