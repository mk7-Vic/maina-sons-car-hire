<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Vehicle;
use Carbon\CarbonInterface;
use InvalidArgumentException;

class BookingAvailabilityService
{
    public function isAvailable(
        Vehicle $vehicle,
        CarbonInterface $pickupAt,
        CarbonInterface $returnAt,
        ?int $ignoreBookingId = null
    ): bool {
        if ($returnAt->lessThanOrEqualTo($pickupAt)) {
            throw new InvalidArgumentException(
                'The return date must be after the pickup date.'
            );
        }

        if ($vehicle->status !== 'available') {
            return false;
        }

        $query = Booking::query()
            ->where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('pickup_at', '<', $returnAt)
            ->where('return_at', '>', $pickupAt);

        if ($ignoreBookingId !== null) {
            $query->where('id', '!=', $ignoreBookingId);
        }

        return ! $query->exists();
    }
}
