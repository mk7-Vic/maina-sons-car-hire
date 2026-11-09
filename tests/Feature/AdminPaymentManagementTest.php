<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        return $admin;
    }

    public function test_guest_cannot_access_payment_management(): void
    {
        $this->get(route('admin.payments.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_cannot_access_payment_management(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.payments.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_payments(): void
    {
        $admin = $this->admin();
        $payment = Payment::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee('Manage Payments')
            ->assertSee($payment->booking->booking_reference)
            ->assertSee($payment->booking->customer_name);
    }

    public function test_admin_can_search_payments(): void
    {
        $admin = $this->admin();

        $matching = Payment::factory()->create([
            'transaction_reference' => 'MPESA-SEARCH-123',
        ]);

        $other = Payment::factory()->create([
            'transaction_reference' => 'MPESA-OTHER-456',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.payments.index', ['search' => 'MPESA-SEARCH-123']))
            ->assertOk()
            ->assertSee($matching->transaction_reference)
            ->assertDontSee($other->transaction_reference);
    }

    public function test_admin_can_filter_payments_by_status(): void
    {
        $admin = $this->admin();

        $pending = Payment::factory()->create(['status' => 'pending']);
        $completed = Payment::factory()->create(['status' => 'completed']);

        $this->actingAs($admin)
            ->get(route('admin.payments.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee($pending->booking->booking_reference)
            ->assertDontSee($completed->booking->booking_reference);
    }

    public function test_admin_can_record_a_payment_and_audit_it(): void
    {
        $admin = $this->admin();
        $booking = Booking::factory()->create(['total_amount' => 50000]);

        $this->actingAs($admin)
            ->post(route('admin.payments.store', $booking), [
                'amount' => 15000,
                'payment_method' => 'mpesa',
                'transaction_reference' => 'MPESA-TEST-001',
            ])
            ->assertRedirect(route('admin.bookings.show', $booking))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'amount' => 15000,
            'payment_method' => 'mpesa',
            'transaction_reference' => 'MPESA-TEST-001',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'payment.created',
        ]);
    }

    public function test_payment_cannot_exceed_outstanding_balance(): void
    {
        $admin = $this->admin();
        $booking = Booking::factory()->create(['total_amount' => 50000]);

        Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 40000,
            'status' => 'completed',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.bookings.show', $booking))
            ->post(route('admin.payments.store', $booking), [
                'amount' => 15000,
                'payment_method' => 'cash',
            ])
            ->assertRedirect(route('admin.bookings.show', $booking))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('payments', 1);
    }

    public function test_payment_submission_validates_required_fields(): void
    {
        $admin = $this->admin();
        $booking = Booking::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.payments.store', $booking), [])
            ->assertSessionHasErrors(['amount', 'payment_method']);
    }

    public function test_admin_can_complete_payment_and_audit_change(): void
    {
        $admin = $this->admin();
        $booking = Booking::factory()->create(['total_amount' => 20000]);
        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 10000,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.payments.status', $payment), [
                'status' => 'completed',
            ])
            ->assertRedirect(route('admin.payments.index'))
            ->assertSessionHas('success');

        $payment->refresh();

        $this->assertSame('completed', $payment->status);
        $this->assertNotNull($payment->paid_at);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'payment.status_changed',
        ]);
    }

    public function test_admin_can_mark_pending_payment_failed(): void
    {
        $admin = $this->admin();
        $payment = Payment::factory()->create(['status' => 'pending']);

        $this->actingAs($admin)
            ->patch(route('admin.payments.status', $payment), [
                'status' => 'failed',
            ])
            ->assertRedirect(route('admin.payments.index'));

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'failed',
        ]);
    }

    public function test_admin_can_refund_completed_payment(): void
    {
        $admin = $this->admin();
        $payment = Payment::factory()->create([
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.payments.status', $payment), [
                'status' => 'refunded',
            ])
            ->assertRedirect(route('admin.payments.index'));

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'refunded',
        ]);
    }

    public function test_invalid_payment_status_transition_is_rejected(): void
    {
        $admin = $this->admin();
        $payment = Payment::factory()->create(['status' => 'failed']);

        $this->actingAs($admin)
            ->from(route('admin.payments.index'))
            ->patch(route('admin.payments.status', $payment), [
                'status' => 'completed',
            ])
            ->assertRedirect(route('admin.payments.index'))
            ->assertSessionHasErrors('status');

        $this->assertSame('failed', $payment->fresh()->status);
    }

    public function test_completed_payments_cannot_exceed_booking_total(): void
    {
        $admin = $this->admin();
        $booking = Booking::factory()->create(['total_amount' => 50000]);

        Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 40000,
            'status' => 'completed',
        ]);

        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 15000,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.payments.index'))
            ->patch(route('admin.payments.status', $payment), [
                'status' => 'completed',
            ])
            ->assertRedirect(route('admin.payments.index'))
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $payment->fresh()->status);
    }
}
