<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_is_logged_in(): void
    {
        $response = $this->post(route('customer.register.store'), [
            'name' => 'Test Customer',
            'email' => 'newcustomer@example.com',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ]);

        $user = User::where('email', 'newcustomer@example.com')->firstOrFail();

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('customer', $user->role);
    }

    public function test_registration_rejects_duplicate_email_and_short_password(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->from(route('customer.register'))
            ->post(route('customer.register.store'), [
                'name' => 'Test Customer',
                'email' => 'taken@example.com',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_customer_can_log_in_and_log_out(): void
    {
        $customer = User::factory()->create();

        $this->post(route('customer.login.store'), [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertRedirect(route('customer.dashboard'));

        $this->assertAuthenticatedAs($customer);

        $this->post(route('customer.logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_customer_login_rejects_admin_accounts(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->from(route('customer.login'))
            ->post(route('customer.login.store'), [
                'email' => $admin->email,
                'password' => 'password',
            ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_guest_cannot_view_customer_bookings(): void
    {
        $this->get(route('customer.dashboard'))
            ->assertRedirect(route('customer.login'));
    }

    public function test_customer_only_sees_their_own_bookings(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();

        $ownBooking = Booking::factory()->create([
            'user_id' => $customer->id,
            'booking_reference' => 'MS-OWNBOOKING1',
        ]);

        Booking::factory()->create([
            'user_id' => $otherCustomer->id,
            'booking_reference' => 'MS-OTHERBOOK01',
        ]);

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee($ownBooking->booking_reference)
            ->assertDontSee('MS-OTHERBOOK01');
    }

    public function test_admin_cannot_access_customer_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)
            ->get(route('customer.dashboard'))
            ->assertForbidden();
    }
}
