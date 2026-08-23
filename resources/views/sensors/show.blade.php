<x-layouts::app :title="$sensor->name">
    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">
                    {{ $sensor->name }}
                </flux:heading>

                <flux:text class="mt-1">
                    {{ $sensor->code }}
                </flux:text>
            </div>

            @if ($sensor->is_active)
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
                <flux:text size="sm">
                    Sensor Code
                </flux:text>

                <flux:heading size="lg" class="mt-1">
                    {{ $sensor->code }}
                </flux:heading>
            </div>

            <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
                <flux:text size="sm">
                    Sensor Type
                </flux:text>

                <flux:heading size="lg" class="mt-1">
                    {{ ucfirst($sensor->type) }}
                </flux:heading>
            </div>

            <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
                <flux:text size="sm">
                    Machine
                </flux:text>

                <flux:heading size="lg" class="mt-1">
                    {{ $sensor->machine->name }}
                </flux:heading>

                <flux:text class="mt-1">
                    {{ $sensor->machine->code }}
                </flux:text>
            </div>

            <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
                <flux:text size="sm">
                    Registered
                </flux:text>

                <flux:heading size="lg" class="mt-1">
                    {{ $sensor->created_at->format('Y-m-d') }}
                </flux:heading>
            </div>
        </div>

        <div class="flex gap-3">
            <flux:button
                variant="ghost"
                :href="route('sensors.index')"
                wire:navigate
            >
                Back
            </flux:button>

            @if (auth()->user()->isAdmin())
                <flux:button
                    variant="primary"
                    :href="route('sensors.edit', $sensor)"
                    wire:navigate
                >
                    Edit Sensor
                </flux:button>
            @endif
        </div>
    </div>
</x-layouts::app>
