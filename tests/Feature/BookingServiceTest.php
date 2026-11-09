<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private function bookingData(Vehicle $vehicle): array
    {
        $pickupBranch = Branch::factory()->create();
        $returnBranch = Branch::factory()->create();

        return [
            'vehicle_id' => $vehicle->id,
            'pickup_branch_id' => $pickupBranch->id,
            'return_branch_id' => $returnBranch->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '0712345678',
            'pickup_at' => '2026-12-01 10:00:00',
            'return_at' => '2026-12-03 10:00:00',
            'notes' => null,
        ];
    }

    public function test_guest_can_create_a_booking(): void
    {
        $vehicle = Vehicle::factory()->create([
            'daily_rate' => 3500,
        ]);

        $booking = app(BookingService::class)->create(
            $this->bookingData($vehicle)
        );

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'user_id' => null,
            'vehicle_id' => $vehicle->id,
            'rental_days' => 2,
            'total_amount' => '7000.00',
            'status' => 'pending',
        ]);

        $this->assertStringStartsWith('MS-', $booking->booking_reference);
    }

    public function test_registered_customer_can_create_a_booking(): void
    {
        $vehicle = Vehicle::factory()->create();
        $user = User::factory()->create();

        $booking = app(BookingService::class)->create(
            $this->bookingData($vehicle),
            $user
        );

        $this->assertSame($user->id, $booking->user_id);
    }

    public function test_partial_rental_days_are_rounded_up(): void
    {
        $vehicle = Vehicle::factory()->create([
            'daily_rate' => 3500,
        ]);

        $data = $this->bookingData($vehicle);
        $data['return_at'] = '2026-12-02 11:00:00';

        $booking = app(BookingService::class)->create($data);

        $this->assertSame(2, $booking->rental_days);
        $this->assertEquals('7000.00', $booking->total_amount);
    }

    public function test_vehicle_cannot_be_booked_twice_for_overlapping_dates(): void
    {
        $vehicle = Vehicle::factory()->create();

        $service = app(BookingService::class);

        $service->create($this->bookingData($vehicle));

        $this->expectException(ValidationException::class);

        $data = $this->bookingData($vehicle);
        $data['pickup_at'] = '2026-12-02 10:00:00';
        $data['return_at'] = '2026-12-04 10:00:00';

        $service->create($data);
    }

    public function test_vehicle_under_maintenance_cannot_be_booked(): void
    {
        $vehicle = Vehicle::factory()->create([
            'status' => 'maintenance',
        ]);

        $this->expectException(ValidationException::class);

        app(BookingService::class)->create(
            $this->bookingData($vehicle)
        );
    }

    public function test_booking_uses_the_vehicle_daily_rate(): void
    {
        $vehicle = Vehicle::factory()->create([
            'daily_rate' => 5000,
        ]);

        $booking = app(BookingService::class)->create(
            $this->bookingData($vehicle)
        );

        $this->assertEquals('5000.00', $booking->daily_rate);
        $this->assertEquals('10000.00', $booking->total_amount);
    }

    public function test_inactive_branch_cannot_be_used_for_booking(): void
    {
        $vehicle = Vehicle::factory()->create();
        $data = $this->bookingData($vehicle);

        Branch::whereKey($data['pickup_branch_id'])->update([
            'is_active' => false,
        ]);

        $this->expectException(ValidationException::class);

        app(BookingService::class)->create($data);
    }

    public function test_return_date_must_be_after_pickup_date(): void
    {
        $vehicle = Vehicle::factory()->create();
        $data = $this->bookingData($vehicle);

        $data['return_at'] = '2026-11-30 10:00:00';

        $this->expectException(ValidationException::class);

        app(BookingService::class)->create($data);
    }

    public function test_failed_booking_does_not_create_an_extra_record(): void
    {
        $vehicle = Vehicle::factory()->create();
        $service = app(BookingService::class);

        $service->create($this->bookingData($vehicle));

        $data = $this->bookingData($vehicle);
        $data['pickup_at'] = '2026-12-02 10:00:00';
        $data['return_at'] = '2026-12-04 10:00:00';

        try {
            $service->create($data);
            $this->fail('Expected the overlapping booking to be rejected.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('bookings', 1);
        }
    }
}
