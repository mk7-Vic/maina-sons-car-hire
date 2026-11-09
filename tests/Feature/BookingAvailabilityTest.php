<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Vehicle;
use App\Services\BookingAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class BookingAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicle_is_available_without_existing_bookings(): void
    {
        $vehicle = Vehicle::factory()->create();

        $available = app(BookingAvailabilityService::class)->isAvailable(
            $vehicle,
            Carbon::parse('2026-12-01 10:00'),
            Carbon::parse('2026-12-03 10:00')
        );

        $this->assertTrue($available);
    }

    public function test_overlapping_pending_booking_blocks_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        Booking::factory()->create([
            'vehicle_id' => $vehicle->id,
            'pickup_at' => '2026-12-01 10:00',
            'return_at' => '2026-12-04 10:00',
            'status' => 'pending',
        ]);

        $available = app(BookingAvailabilityService::class)->isAvailable(
            $vehicle,
            Carbon::parse('2026-12-02 10:00'),
            Carbon::parse('2026-12-05 10:00')
        );

        $this->assertFalse($available);
    }

    public function test_overlapping_confirmed_booking_blocks_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        Booking::factory()->create([
            'vehicle_id' => $vehicle->id,
            'pickup_at' => '2026-12-01 10:00',
            'return_at' => '2026-12-04 10:00',
            'status' => 'confirmed',
        ]);

        $available = app(BookingAvailabilityService::class)->isAvailable(
            $vehicle,
            Carbon::parse('2026-12-02 10:00'),
            Carbon::parse('2026-12-03 10:00')
        );

        $this->assertFalse($available);
    }

    public function test_adjacent_bookings_are_allowed(): void
    {
        $vehicle = Vehicle::factory()->create();

        Booking::factory()->create([
            'vehicle_id' => $vehicle->id,
            'pickup_at' => '2026-12-01 10:00',
            'return_at' => '2026-12-03 10:00',
            'status' => 'confirmed',
        ]);

        $service = app(BookingAvailabilityService::class);

        $this->assertTrue(
            $service->isAvailable(
                $vehicle,
                Carbon::parse('2026-12-03 10:00'),
                Carbon::parse('2026-12-05 10:00')
            )
        );

        $this->assertTrue(
            $service->isAvailable(
                $vehicle,
                Carbon::parse('2026-11-29 10:00'),
                Carbon::parse('2026-12-01 10:00')
            )
        );
    }

    public function test_cancelled_and_completed_bookings_do_not_block_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        foreach (['cancelled', 'completed'] as $status) {
            Booking::factory()->create([
                'vehicle_id' => $vehicle->id,
                'pickup_at' => '2026-12-01 10:00',
                'return_at' => '2026-12-04 10:00',
                'status' => $status,
            ]);
        }

        $available = app(BookingAvailabilityService::class)->isAvailable(
            $vehicle,
            Carbon::parse('2026-12-02 10:00'),
            Carbon::parse('2026-12-03 10:00')
        );

        $this->assertTrue($available);
    }

    public function test_existing_booking_can_be_excluded_when_checking_its_own_dates(): void
    {
        $vehicle = Vehicle::factory()->create();

        $booking = Booking::factory()->create([
            'vehicle_id' => $vehicle->id,
            'pickup_at' => '2026-12-01 10:00',
            'return_at' => '2026-12-04 10:00',
            'status' => 'confirmed',
        ]);

        $available = app(BookingAvailabilityService::class)->isAvailable(
            $vehicle,
            Carbon::parse('2026-12-01 10:00'),
            Carbon::parse('2026-12-04 10:00'),
            $booking->id
        );

        $this->assertTrue($available);
    }

    public function test_return_date_must_be_after_pickup_date(): void
    {
        $vehicle = Vehicle::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        app(BookingAvailabilityService::class)->isAvailable(
            $vehicle,
            Carbon::parse('2026-12-03 10:00'),
            Carbon::parse('2026-12-01 10:00')
        );
    }

    public function test_vehicle_under_maintenance_is_not_available(): void
    {
        $vehicle = Vehicle::factory()->create([
            'status' => 'maintenance',
        ]);

        $available = app(BookingAvailabilityService::class)->isAvailable(
            $vehicle,
            Carbon::parse('2026-12-01 10:00'),
            Carbon::parse('2026-12-03 10:00')
        );

        $this->assertFalse($available);
    }

    public function test_unavailable_vehicle_cannot_be_booked(): void
    {
        $vehicle = Vehicle::factory()->create([
            'status' => 'unavailable',
        ]);

        $available = app(BookingAvailabilityService::class)->isAvailable(
            $vehicle,
            Carbon::parse('2026-12-01 10:00'),
            Carbon::parse('2026-12-03 10:00')
        );

        $this->assertFalse($available);
    }
}
