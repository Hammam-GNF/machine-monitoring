<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSensorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'machine_id' => [
                'required',
                'integer',
                'exists:machines,id',
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('sensors', 'code')
                    ->where(
                        fn ($query) => $query->where(
                            'machine_id',
                            $this->input('machine_id')
                        )
                    ),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'type' => [
                'required',
                'string',
                'max:30',
            ],
            'is_active' => [
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
