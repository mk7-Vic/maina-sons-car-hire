<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private function bookingData(): array
    {
        $vehicle = Vehicle::factory()->create();
        $pickupBranch = Branch::factory()->create();
        $returnBranch = Branch::factory()->create();

        return [
            'vehicle_id' => $vehicle->id,
            'pickup_branch_id' => $pickupBranch->id,
            'return_branch_id' => $returnBranch->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '0712345678',
            'pickup_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'return_at' => now()->addDays(12)->format('Y-m-d H:i:s'),
            'notes' => 'Test booking',
        ];
    }

    public function test_guest_can_submit_booking_and_view_confirmation(): void
    {
        $data = $this->bookingData();

        $response = $this->post(route('bookings.store'), $data);
        $booking = Booking::firstOrFail();

        $response->assertRedirect(
            route('bookings.confirmation', [
                'reference' => $booking->booking_reference,
            ])
        );

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'customer_email' => 'customer@example.com',
            'status' => 'pending',
        ]);

        $confirmation = $this->get(
            route('bookings.confirmation', [
                'reference' => $booking->booking_reference,
            ])
        );

        $confirmation->assertOk();
        $confirmation->assertSee($booking->booking_reference);
        $confirmation->assertSee($booking->vehicle->make);
        $confirmation->assertSee($booking->pickupBranch->name);
    }

    public function test_invalid_booking_submission_is_rejected(): void
    {
        $data = $this->bookingData();
        $data['customer_email'] = 'not-an-email';

        $response = $this->from('/')
            ->post(route('bookings.store'), $data);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('customer_email');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_booking_with_inactive_branch_is_rejected(): void
    {
        $data = $this->bookingData();

        Branch::whereKey($data['pickup_branch_id'])->update([
            'is_active' => false,
        ]);

        $response = $this->from('/')
            ->post(route('bookings.store'), $data);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('pickup_branch_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_vehicle_details_page_displays_booking_form_and_active_branches(): void
    {
        $vehicle = Vehicle::factory()->create();

        Branch::factory()->create([
            'name' => 'Active Test Branch',
            'is_active' => true,
        ]);

        Branch::factory()->create([
            'name' => 'Inactive Test Branch',
            'is_active' => false,
        ]);

        $response = $this->get(route('vehicles.show', $vehicle));

        $response->assertOk()
            ->assertSee('Book This Vehicle')
            ->assertSee('Active Test Branch')
            ->assertDontSee('Inactive Test Branch')
            ->assertSee('name="vehicle_id"', false);
    }

    public function test_unavailable_vehicle_details_page_returns_not_found(): void
    {
        $vehicle = Vehicle::factory()->create([
            'status' => 'maintenance',
        ]);

        $this->get(route('vehicles.show', $vehicle))
            ->assertNotFound();
    }
}
