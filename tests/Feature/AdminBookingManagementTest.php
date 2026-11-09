<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_bookings(): void
    {
        $response = $this->get(route('admin.bookings.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_customer_cannot_access_admin_bookings(): void
    {
        $customer = User::factory()->create();

        $response = $this->actingAs($customer)
            ->get(route('admin.bookings.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_view_booking_information(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $booking = Booking::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.bookings.index'));

        $response->assertOk();
        $response->assertSee('Manage Bookings');
        $response->assertSee($booking->booking_reference);
        $response->assertSee($booking->customer_name);
        $response->assertSee($booking->vehicle->registration_number);
        $response->assertSee($booking->pickupBranch->name);
        $response->assertSee($booking->returnBranch->name);
    }

    public function test_bookings_are_paginated_ten_per_page(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $bookings = Booking::factory()->count(12)->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.bookings.index'));

        $response->assertOk();
        $response->assertSee($bookings[11]->booking_reference);
        $response->assertDontSee($bookings[0]->booking_reference);
    }

    public function test_admin_can_search_bookings_by_reference(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $matching = Booking::factory()->create([
            'booking_reference' => 'MS-SEARCH-123',
        ]);

        $other = Booking::factory()->create([
            'booking_reference' => 'MS-OTHER-456',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.bookings.index', [
                'search' => 'MS-SEARCH-123',
            ]));

        $response->assertOk();
        $response->assertSee($matching->booking_reference);
        $response->assertDontSee($other->booking_reference);
    }

    public function test_admin_can_search_bookings_by_customer_name(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $matching = Booking::factory()->create([
            'customer_name' => 'Victor Searchable',
        ]);

        $other = Booking::factory()->create([
            'customer_name' => 'Another Customer',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.bookings.index', [
                'search' => 'Victor Searchable',
            ]));

        $response->assertOk();
        $response->assertSee($matching->customer_name);
        $response->assertDontSee($other->customer_name);
    }

    public function test_admin_can_search_bookings_by_customer_email(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $matching = Booking::factory()->create([
            'customer_email' => 'search@example.com',
        ]);

        $other = Booking::factory()->create([
            'customer_email' => 'other@example.com',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.bookings.index', [
                'search' => 'search@example.com',
            ]));

        $response->assertOk();
        $response->assertSee($matching->booking_reference);
        $response->assertDontSee($other->booking_reference);
    }

    public function test_admin_can_filter_bookings_by_status(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $pending = Booking::factory()->create([
            'status' => 'pending',
        ]);

        $confirmed = Booking::factory()->create([
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.bookings.index', [
                'status' => 'pending',
            ]));

        $response->assertOk();
        $response->assertSee($pending->booking_reference);
        $response->assertDontSee($confirmed->booking_reference);
    }

    public function test_admin_can_combine_booking_search_and_status_filter(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $matching = Booking::factory()->create([
            'customer_name' => 'Victor Combined',
            'status' => 'confirmed',
        ]);

        $wrongStatus = Booking::factory()->create([
            'customer_name' => 'Victor Combined',
            'status' => 'pending',
        ]);

        $wrongName = Booking::factory()->create([
            'customer_name' => 'Another Customer',
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.bookings.index', [
                'search' => 'Victor Combined',
                'status' => 'confirmed',
            ]));

        $response->assertOk();
        $response->assertSee($matching->booking_reference);
        $response->assertDontSee($wrongStatus->booking_reference);
        $response->assertDontSee($wrongName->booking_reference);
    }

    public function test_invalid_booking_status_filter_is_rejected(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $response = $this->from(route('admin.bookings.index'))
            ->actingAs($admin)
            ->get(route('admin.bookings.index', [
                'status' => 'invalid-status',
            ]));

        $response->assertRedirect(route('admin.bookings.index'));
        $response->assertSessionHasErrors('status');
    }

    public function test_admin_can_view_booking_details(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $booking = Booking::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.bookings.show', $booking));

        $response->assertOk();
        $response->assertSee('Booking Details');
        $response->assertSee($booking->booking_reference);
        $response->assertSee($booking->customer_name);
        $response->assertSee($booking->customer_email);
        $response->assertSee($booking->vehicle->registration_number);
        $response->assertSee('Manage Booking Status');
    }

    public function test_guest_cannot_view_booking_details(): void
    {
        $booking = Booking::factory()->create();

        $response = $this->get(route('admin.bookings.show', $booking));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_customer_cannot_view_booking_details(): void
    {
        $customer = User::factory()->create();
        $booking = Booking::factory()->create();

        $response = $this->actingAs($customer)
            ->get(route('admin.bookings.show', $booking));

        $response->assertForbidden();
    }

    public function test_admin_can_confirm_pending_booking_and_audit_change(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $booking = Booking::factory()->create([
            'status' => 'pending',
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->actingAs($admin)
            ->patch(route('admin.bookings.status', $booking), [
                'status' => 'confirmed',
            ])
            ->assertRedirect(route('admin.bookings.show', $booking))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'booking.status_changed',
            'auditable_type' => $booking->getMorphClass(),
            'auditable_id' => $booking->id,
            'ip_address' => '203.0.113.10',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'description' => "Booking {$booking->booking_reference} status changed from pending to confirmed.",
        ]);
    }

    public function test_admin_can_cancel_pending_booking(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $booking = Booking::factory()->create([
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.bookings.status', $booking), [
                'status' => 'cancelled',
            ])
            ->assertRedirect(route('admin.bookings.show', $booking));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'booking.status_changed',
            'auditable_id' => $booking->id,
        ]);
    }

    public function test_admin_can_complete_confirmed_booking(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $booking = Booking::factory()->create([
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.bookings.status', $booking), [
                'status' => 'completed',
            ])
            ->assertRedirect(route('admin.bookings.show', $booking));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'completed',
        ]);
    }

    public function test_admin_can_cancel_confirmed_booking(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $booking = Booking::factory()->create([
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.bookings.status', $booking), [
                'status' => 'cancelled',
            ])
            ->assertRedirect(route('admin.bookings.show', $booking));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_completed_booking_cannot_change_status(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $booking = Booking::factory()->create([
            'status' => 'completed',
        ]);

        $this->from(route('admin.bookings.show', $booking))
            ->actingAs($admin)
            ->patch(route('admin.bookings.status', $booking), [
                'status' => 'cancelled',
            ])
            ->assertRedirect(route('admin.bookings.show', $booking))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseMissing('audit_logs', [
            'auditable_id' => $booking->id,
        ]);
    }

    public function test_cancelled_booking_cannot_change_status(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $booking = Booking::factory()->create([
            'status' => 'cancelled',
        ]);

        $this->from(route('admin.bookings.show', $booking))
            ->actingAs($admin)
            ->patch(route('admin.bookings.status', $booking), [
                'status' => 'confirmed',
            ])
            ->assertRedirect(route('admin.bookings.show', $booking))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseMissing('audit_logs', [
            'auditable_id' => $booking->id,
        ]);
    }

    public function test_invalid_status_value_is_rejected(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $booking = Booking::factory()->create([
            'status' => 'pending',
        ]);

        $this->from(route('admin.bookings.show', $booking))
            ->actingAs($admin)
            ->patch(route('admin.bookings.status', $booking), [
                'status' => 'in_progress',
            ])
            ->assertRedirect(route('admin.bookings.show', $booking))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseMissing('audit_logs', [
            'auditable_id' => $booking->id,
        ]);
    }

    public function test_customer_cannot_update_booking_status(): void
    {
        $customer = User::factory()->create();

        $booking = Booking::factory()->create([
            'status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->patch(route('admin.bookings.status', $booking), [
                'status' => 'confirmed',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseMissing('audit_logs', [
            'auditable_id' => $booking->id,
        ]);
    }

    public function test_guest_cannot_update_booking_status(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'pending',
        ]);

        $this->patch(route('admin.bookings.status', $booking), [
            'status' => 'confirmed',
        ])->assertRedirect(route('admin.login'));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'pending',
        ]);
    }
}
