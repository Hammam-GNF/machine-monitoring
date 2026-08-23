<x-layouts::app :title="__('Edit Machine')">
    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6">
        <div>
            <flux:heading size="xl">Edit Machine</flux:heading>
            <flux:text class="mt-1">
                Update machine information.
            </flux:text>
        </div>

        <form
            method="POST"
            action="{{ route('machines.update', $machine) }}"
            class="flex flex-col gap-6"
        >
            @csrf
            @method('PUT')

            <flux:input
                name="code"
                label="Machine Code"
                :value="old('code', $machine->code)"
                required
            />

            @error('code')
                <flux:text variant="danger">{{ $message }}</flux:text>
            @enderror

            <flux:input
                name="name"
                label="Machine Name"
                :value="old('name', $machine->name)"
                required
            />

            @error('name')
                <flux:text variant="danger">{{ $message }}</flux:text>
            @enderror

            <flux:input
                name="location"
                label="Location / Line"
                :value="old('location', $machine->location)"
                required
            />

            @error('location')
                <flux:text variant="danger">{{ $message }}</flux:text>
            @enderror

            <flux:input
                name="machine_type"
                label="Machine Type"
                :value="old('machine_type', $machine->machine_type)"
                required
            />

            @error('machine_type')
                <flux:text variant="danger">{{ $message }}</flux:text>
            @enderror

            <flux:input
                type="date"
                name="installed_at"
                label="Installation Date"
                :value="old('installed_at', $machine->installed_at->format('Y-m-d'))"
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
                    @checked(old('is_active', $machine->is_active))
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
                    Update Machine
                </flux:button>

                <flux:button
                    variant="ghost"
                    :href="route('machines.show', $machine)"
                    wire:navigate
                >
                    Cancel
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
