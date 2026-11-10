<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $vehicle = $this->route('vehicle');

        return [
            'vehicle_category_id' => ['required', 'exists:vehicle_categories,id'],
            'registration_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicles', 'registration_number')->ignore($vehicle),
            ],
            'make' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'color' => ['nullable', 'string', 'max:100'],
            'transmission' => ['nullable', 'string', 'max:50'],
            'fuel_type' => ['nullable', 'string', 'max:50'],
            'seats' => ['required', 'integer', 'between:1,255'],
            'daily_rate' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['available', 'maintenance', 'unavailable'])],
        ];
    }
}
