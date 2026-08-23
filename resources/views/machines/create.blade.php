<x-layouts::app :title="__('Create Machine')">
    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6">
        <div>
            <flux:heading size="xl">Create Machine</flux:heading>
            <flux:text class="mt-1">
                Register a new machine.
            </flux:text>
        </div>

        <form
            method="POST"
            action="{{ route('machines.store') }}"
            class="flex flex-col gap-6"
        >
            @csrf

            <flux:input
                name="code"
                label="Machine Code"
                placeholder="MC-001"
                :value="old('code')"
                required
            />

            @error('code')
                <flux:text variant="danger">{{ $message }}</flux:text>
            @enderror

            <flux:input
                name="name"
                label="Machine Name"
                placeholder="CNC Machine A"
                :value="old('name')"
                required
            />

            @error('name')
                <flux:text variant="danger">{{ $message }}</flux:text>
            @enderror

            <flux:input
                name="location"
                label="Location / Line"
                placeholder="Production Line 1"
                :value="old('location')"
                required
            />

            @error('location')
                <flux:text variant="danger">{{ $message }}</flux:text>
            @enderror

            <flux:input
                name="machine_type"
                label="Machine Type"
                placeholder="CNC"
                :value="old('machine_type')"
                required
            />

            @error('machine_type')
                <flux:text variant="danger">{{ $message }}</flux:text>
            @enderror

            <flux:input
                type="date"
                name="installed_at"
                label="Installation Date"
                :value="old('installed_at')"
                required
            />

            @error('installed_at')
                <flux:text variant="danger">{{ $message }}</flux:text>
            @enderror

            <label class="flex items-center gap-3">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', true))
                    class="rounded border-neutral-300"
                >

                <span class="text-sm">
                    Active
                </span>
            </label>

            @error('is_active')
                <flux:text variant="danger">{{ $message }}</flux:text>
            @enderror

            <div class="flex gap-3">
                <flux:button
                    type="submit"
                    variant="primary"
                >
                    Create Machine
                </flux:button>

                <flux:button
                    variant="ghost"
                    :href="route('machines.index')"
                    wire:navigate
                >
                    Cancel
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
