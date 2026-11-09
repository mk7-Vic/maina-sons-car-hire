<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private BookingAvailabilityService $availability
    ) {}

    public function create(array $data, ?User $user = null): Booking
    {
        return DB::transaction(function () use ($data, $user) {
            // Lock the vehicle to prevent simultaneous booking attempts.
            $vehicle = Vehicle::query()
                ->lockForUpdate()
                ->findOrFail($data['vehicle_id']);

            $pickupAt = Carbon::parse($data['pickup_at']);
            $returnAt = Carbon::parse($data['return_at']);

            if ($returnAt->lessThanOrEqualTo($pickupAt)) {
                throw ValidationException::withMessages([
                    'return_at' => 'The return date must be after the pickup date.',
                ]);
            }

            // Both branches must exist and be active.
            foreach ([
                'pickup_branch_id',
                'return_branch_id',
            ] as $branchField) {
                $active = Branch::query()
                    ->whereKey($data[$branchField])
                    ->where('is_active', true)
                    ->exists();

                if (! $active) {
                    throw ValidationException::withMessages([
                        $branchField => 'Please select an active branch.',
                    ]);
                }
            }

            // Check operational status and date conflicts.
            if (! $this->availability->isAvailable(
                $vehicle,
                $pickupAt,
                $returnAt
            )) {
                throw ValidationException::withMessages([
                    'vehicle_id' => 'This vehicle is not available for the selected dates.',
                ]);
            }

            // Calculate rental duration in 24-hour periods.
            $seconds = $returnAt->getTimestamp() - $pickupAt->getTimestamp();
            $rentalDays = (int) ceil($seconds / 86400);

            // Calculate the total using integer cents.
            $dailyRateCents = (int) round(
                (float) $vehicle->daily_rate * 100
            );

            $totalAmount = number_format(
                ($dailyRateCents * $rentalDays) / 100,
                2,
                '.',
                ''
            );

            // Generate a unique booking reference.
            do {
                $reference = 'MS-'.Str::upper(Str::random(10));
            } while (Booking::where(
                'booking_reference',
                $reference
            )->exists());

            return Booking::create([
                'booking_reference' => $reference,
                'user_id' => $user?->id,
                'vehicle_id' => $vehicle->id,
                'pickup_branch_id' => $data['pickup_branch_id'],
                'return_branch_id' => $data['return_branch_id'],
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'pickup_at' => $pickupAt,
                'return_at' => $returnAt,
                'rental_days' => $rentalDays,
                'daily_rate' => $vehicle->daily_rate,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }
}
