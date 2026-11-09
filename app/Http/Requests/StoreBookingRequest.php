<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => [
                'required',
                'integer',
                Rule::exists('vehicles', 'id'),
            ],

            'pickup_branch_id' => [
                'required',
                'integer',
                Rule::exists('branches', 'id')
                    ->where('is_active', true),
            ],

            'return_branch_id' => [
                'required',
                'integer',
                Rule::exists('branches', 'id')
                    ->where('is_active', true),
            ],

            'customer_name' => [
                'required',
                'string',
                'max:255',
            ],

            'customer_email' => [
                'required',
                'email',
                'max:255',
            ],

            'customer_phone' => [
                'required',
                'string',
                'max:30',
            ],

            'pickup_at' => [
                'required',
                'date',
                'after:now',
            ],

            'return_at' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $pickupAt = strtotime((string) $this->input('pickup_at'));
                    $returnAt = strtotime((string) $value);

                    if (
                        $pickupAt !== false &&
                        $returnAt !== false &&
                        $returnAt <= $pickupAt
                    ) {
                        $fail('The return date must be after the pickup date.');
                    }
                },
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
