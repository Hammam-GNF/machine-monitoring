<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMachineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $machine = $this->route('machine');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('machines', 'code')->ignore($machine),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'location' => [
                'required',
                'string',
                'max:100',
            ],
            'machine_type' => [
                'required',
                'string',
                'max:100',
            ],
            'installed_at' => [
                'required',
                'date',
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
