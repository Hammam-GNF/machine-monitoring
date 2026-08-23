<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSensorDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id' => [
                'required',
                'uuid',
            ],

            'machine_id' => [
                'required',
                'integer',
                'exists:machines,id',
            ],

            'sensor_id' => [
                'required',
                'integer',
                Rule::exists('sensors', 'id')
                    ->where(
                        fn ($query) => $query->where(
                            'machine_id',
                            $this->input('machine_id')
                        )
                    ),
            ],

            'status' => [
                'required',
                'string',
                'in:ON,OFF',
            ],

            'temperature' => [
                'nullable',
                'numeric',
                'between:-9999.99,9999.99',
            ],

            'output' => [
                'required',
                'integer',
                'min:0',
            ],

            'recorded_at' => [
                'required',
                'date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'sensor_id.exists' => 'The selected sensor does not belong to the selected machine.',
        ];
    }
}
