<x-layouts::app :title="__('Create Sensor')">
    <div class="mx-auto flex w-full max-w-3xl flex-col gap-6">
        <div>
            <flux:heading size="xl">Create Sensor</flux:heading>

            <flux:text class="mt-1">
                Register a new sensor for a machine.
            </flux:text>
        </div>

        <form
            method="POST"
            action="{{ route('sensors.store') }}"
            class="flex flex-col gap-6"
        >
            @csrf

            <flux:select
                name="machine_id"
                label="Machine"
                required
            >
                <option value="">Select machine</option>

                @foreach ($machines as $machine)
                    <option
                        value="{{ $machine->id }}"
                        @selected(old('machine_id') == $machine->id)
                    >
                        {{ $machine->code }} - {{ $machine->name }}
                    </option>
                @endforeach
            </flux:select>

            @error('machine_id')
                <flux:text variant="danger">
                    {{ $message }}
                </flux:text>
            @enderror

            <flux:input
                name="code"
                label="Sensor Code"
                placeholder="SNS-001"
                :value="old('code')"
                required
            />

            @error('code')
                <flux:text variant="danger">
                    {{ $message }}
                </flux:text>
            @enderror

            <flux:input
                name="name"
                label="Sensor Name"
                placeholder="Temperature Sensor"
                :value="old('name')"
                required
            />

            @error('name')
                <flux:text variant="danger">
                    {{ $message }}
                </flux:text>
            @enderror

            <flux:input
                name="type"
                label="Sensor Type"
                placeholder="temperature"
                :value="old('type')"
                required
            />

            @error('type')
                <flux:text variant="danger">
                    {{ $message }}
                </flux:text>
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
                <flux:text variant="danger">
                    {{ $message }}
                </flux:text>
            @enderror

            <div class="flex gap-3">
                <flux:button
                    type="submit"
                    variant="primary"
                >
                    Create Sensor
                </flux:button>

                <flux:button
                    variant="ghost"
                    :href="route('sensors.index')"
                    wire:navigate
                >
                    Cancel
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
